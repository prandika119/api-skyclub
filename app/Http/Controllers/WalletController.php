<?php

namespace App\Http\Controllers;

use App\Http\Requests\TopupRequest;
use App\Http\Requests\TransferRequest;
use App\Http\Requests\WithdrawRequest;
use App\Http\Resources\RecentTransactionResource;
use App\Models\RecentTransaction;
use App\Models\User;
use App\Models\Wallet;
use http\Client\Response;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Display a recents transaction of user.
     */
    public function getRecentTransaction()
    {
        $data = auth()->user()->recentTransactions()->with(['user', 'recipient'])->latest()->get();
        return response([
            'message' => 'Get Recent Transaction',
            'data' => RecentTransactionResource::collection($data),
        ]);
    }

    /**
     * Topup the wallet.
     * @param TopupRequest $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     *
     */
    public function topup(TopupRequest $request)
    {
        $data = $request->validated();
        $user = auth()->user();
        RecentTransaction::create([
            'user_id' => $user->id,
            'wallet_id' => $user->wallet->id,
            'transaction_type' => 'topup',
            'amount' => $data['amount'],
            'bank_ewallet' => $data['bank_ewallet'],
            'number' => $data['number'],
        ]);
        $user->wallet->increment('balance', $data['amount']);
        return response([
            'message' => 'Topup Success',
            'data' => [
                'balance' => $user->wallet->balance,
            ]
        ]);
    }

    /**
     * Transfer the wallet.
     * @param TransferRequest $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     *
     */
    public function transfer(TransferRequest $request)
    {
        $data = $request->validated();
        $user = auth()->user();
        $recepient = User::where('id', $data['recipient_id'])->first();
        // Check if recipient is the same as user
        if ($recepient->id == $user->id) {
            return response([
                'message' => 'Anda tidak dapat mentransfer ke diri sendiri',
            ], 400);
        }

        // Check if recipient is not found
        if (!$recepient) {
            return response([
                'message' => 'Penerima tidak ditemukan',
            ], 404);
        }

        // Check if balance is sufficient (cukup)
        if ($user->wallet->balance < $data['amount']) {
            return response([
                'message' => 'Saldo tidak cukup',
            ], 400);
        }

        $recentTrans =RecentTransaction::create([
            'user_id' => $user->id,
            'recipient_id' => $data['recipient_id'],
            'wallet_id' => $user->wallet->id,
            'transaction_type' => 'transfer',
            'amount' => $data['amount'],
            'bank_ewallet' => $data['bank_ewallet'],
            'number' => $data['number'],
        ]);


        $user->wallet->decrement('balance', $data['amount']);

        $recepient->wallet->increment('balance', $data['amount']);
        return response([
            'message' => 'Transfer Success',
            'data' => [
                'recipient_id' => $data['recipient_id'],
                'balance' => $user->wallet->balance,
            ]
        ]);
    }

    /**
     * Withdraw the wallet.
     * @param WithdrawRequest $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function withdraw(WithdrawRequest $request)
    {
        $data = $request->validated();
        $user = auth()->user();

        // Check if amount is less than 10,000
        if ($data['amount'] < 10000) {
            return response([
                'message' => 'Topup minimal Rp 10.000',
            ], 400);
        }

        // Check if balance is sufficient (cukup)
        if ($user->wallet->balance < $data['amount']) {
            return response([
                'message' => 'Saldo tidak cukup',
            ], 400);
        }

        RecentTransaction::create([
            'user_id' => $user->id,
            'wallet_id' => $user->wallet->id,
            'transaction_type' => 'withdraw',
            'amount' => $data['amount'],
            'bank_ewallet' => $data['bank_ewallet'],
            'number' => $data['number'],
        ]);

        $user->wallet->decrement('balance', $data['amount']);
        return response([
            'message' => 'Withdraw Success',
            'data' => [
                'withdrawn_amount' => $data['amount'],
                'balance' => $user->wallet->balance,
            ]
        ]);
    }
}
