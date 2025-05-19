<?php

namespace App\Http\Controllers;

use App\Events\AcceptedCancelScheduleEvent;
use App\Events\RejectedCancelScheduleEvent;
use App\Events\RequestCancelScheduleEvent;
use App\Http\Resources\CancelRequestResource;
use App\Models\ListBooking;
use App\Models\RequestCancel;
use App\Http\Requests\StoreRequestCancelRequest;
use App\Http\Requests\UpdateRequestCancelRequest;
use Carbon\Carbon;

class RequestCancelController extends Controller
{
    /**
     * Display a listing of Request Cancel Booking.
     */
    public function index()
    {
        $cancelRequest = RequestCancel::where('reply', null)->latest()->get();
        if ($cancelRequest->isEmpty()) {
            return response([
                'message' => 'Tidak Ada Request Cancel Booking',
                'data' => []
            ]);
        }
        return response([
            'message' => 'List Request Cancel Booking',
            'data' => CancelRequestResource::collection($cancelRequest)
        ]);
    }

    /**
     * Add Request Cancel Booking
     */
    public function addRequest(ListBooking  $listBooking,StoreRequestCancelRequest $request)
    {
        $data = $request->validated();
        if ($listBooking->status_request){
            return response([
                'message' => 'Bad Request',
                'error' => 'Tidak bisa mengajukan pembatalan, permintaan sudah diajukan'
            ], 400);
        }
        $user = auth()->user();
        $dayDifference = Carbon::now()->diffInDays(Carbon::parse($listBooking->date), false);  // hasil negatif jika sudah lewat
        if ($dayDifference < 7) {
            return response([
                'message' => 'Bad Request',
                'error' => 'Tidak bisa mengajukan pembatalan, batas waktu pengajuan pembatalan adalah 7 hari sebelum tanggal booking'
            ], 400);
        }
        $listBooking->update([
            'status_request' => 'Cancel Request',
        ]);
        RequestCancel::create([
            'list_booking_id' => $listBooking->id,
            'user_id' => $user->id,
            'reason' => $data['reason'],
        ]);
        event(new RequestCancelScheduleEvent($listBooking));
        return response([
            'message' => 'Request Cancel Booking Success',
        ], 201);
    }

    /**
     * Accept Request Cancel Booking
     */
    public function acceptRequest(UpdateRequestCancelRequest $request, RequestCancel $requestCancel)
    {
        $data = $request->validated();
        $user = $requestCancel->listBooking->user;
        $requestCancel->update([
            'reply' => $data['reply'],
        ]);
        $requestCancel->listBooking->update([
            "status_request" => "Canceled"
        ]);
        $user->recentTransactions()->create([
            'user_id' => $user->id,
            'wallet_id' => $user->wallet->id,
            'transaction_type' => 'booking',
            'amount' => $requestCancel->listBooking->price,
        ]);
        $requestCancel->listBooking->user->wallet->increment('balance', $requestCancel->listBooking->total_price);

        event(new AcceptedCancelScheduleEvent($requestCancel->listBooking));
        return response([
            'message' => 'Request Cancel Booking Accepted',
            'data' => new CancelRequestResource($requestCancel)
        ]);
    }

    /**
     * Reject Request Cancel Booking
     */
    public function rejectRequest(UpdateRequestCancelRequest $request, RequestCancel $requestCancel)
    {
        $data = $request->validated();
        $requestCancel->update([
            'reply' => $data['reply'],
        ]);
        $requestCancel->listBooking->update([
            'status_request' => 'Cancel Rejected'
        ]);
        event(new RejectedCancelScheduleEvent($requestCancel->listBooking));
        return response([
            'message' => 'Request Cancel Booking Rejected',
            'data' => new CancelRequestResource($requestCancel)
        ]);
    }
}
