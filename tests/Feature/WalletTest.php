<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WalletTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testTopUp():void
    {
        $user = $this->AuthUser();
        $response = $this->postJson('/api/wallet/topup', [
            'amount' => 10000,
            'bank_ewallet' => 'Bank BCA',
            'number' => '1234567890',
        ]);
        dump($response->getContent());
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Topup Success',
                'data' => [
                    'balance' => 10000,
                ]
            ]);
        $this->assertDatabaseHas('recent_transactions', [
            'user_id' => $user->id,
            'transaction_type' => 'topup',
            'amount' => 10000,
            'bank_ewallet' => 'Bank BCA',
            'number' => '1234567890',
        ]);
    }

    public function testWalletTransfer()
    {
        $user = $this->AuthUser();
        $user2 = $this->AuthUser2();
        $user2->wallet()->update([
            'balance' => 10000,
        ]);
        $response = $this->postJson('/api/wallet/transfer', [
            'recipient_id' => $user->id,
            'amount' => 10000,
            'bank_ewallet' => 'Bank BCA',
            'number' => '1234567890',
        ]);
        dump($response->getContent());
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Transfer Success',
                'data' => [
                    'recipient_id' => $user->id,
                    'balance' => 0,
                ]
            ]);
        $this->assertDatabaseHas('recent_transactions', [
            'user_id' => $user2->id,
            'recipient_id' => $user->id,
            'transaction_type' => 'transfer',
            'amount' => 10000,
            'bank_ewallet' => 'Bank BCA',
            'number' => '1234567890',
        ]);
    }

    public function testWalletTransferNotEnough()
    {
        $user = $this->AuthUser();
        $user2 = $this->AuthUser2();
        $user2->wallet()->update([
            'balance' => 5000,
        ]);
        $response = $this->postJson('/api/wallet/transfer', [
            'recipient_id' => $user->id,
            'amount' => 10000,
            'bank_ewallet' => 'Bank BCA',
            'number' => '1234567890',
        ]);
        dump($response->getContent());
        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Saldo tidak cukup',
            ]);
    }

    public function testTransferNotSameUser()
    {
        $user = $this->AuthUser();

        $response = $this->postJson('/api/wallet/transfer', [
            'recipient_id' => $user->id,
            'amount' => 10000,
            'bank_ewallet' => 'Bank BCA',
            'number' => '1234567890',
        ]);
        dump($response->getContent());
        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Anda tidak dapat mentransfer ke diri sendiri',
            ]);
    }

    public function testWalletWithdraw()
    {
        $user = $this->AuthUser();
        $user->wallet()->update([
            'balance' => 10000,
        ]);
        $response = $this->postJson('/api/wallet/withdraw', [
            'amount' => 10000,
            'bank_ewallet' => 'Bank BCA',
            'number' => '1234567890',
        ]);
        dump($response->getContent());
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Withdraw Success',
                'data' => [
                    'balance' => 0,
                ]
            ]);
        $this->assertDatabaseHas('recent_transactions', [
            'user_id' => $user->id,
            'transaction_type' => 'withdraw',
            'amount' => 10000,
            'bank_ewallet' => 'Bank BCA',
            'number' => '1234567890',
        ]);
    }

    public function testWalletWithdrawUnder10000()
    {
        $user = $this->AuthUser();
        $user->wallet()->update([
            'balance' => 10000,
        ]);
        $response = $this->postJson('/api/wallet/withdraw', [
            'amount' => 5000,
            'bank_ewallet' => 'Bank BCA',
            'number' => '1234567890',
        ]);
        dump($response->getContent());
        $response->assertStatus(422);
        $this->assertDatabaseMissing('recent_transactions', [
            'user_id' => $user->id,
            'transaction_type' => 'withdraw',
            'amount' => 5000,
            'bank_ewallet' => 'Bank BCA',
            'number' => '1234567890',
        ]);
    }

    public function testWalletWithdrawNotEnough()
    {
        $user = $this->AuthUser();
        $user->wallet()->update([
            'balance' => 5000,
        ]);
        $response = $this->postJson('/api/wallet/withdraw', [
            'amount' => 10000,
            'bank_ewallet' => 'Bank BCA',
            'number' => '1234567890',
        ]);
        dump($response->getContent());
        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Saldo tidak cukup',
            ]);
    }

    public function testGetRecentTransaction()
    {
        $user = $this->AuthUser();
        $recent = $this->topupWallet($user, 10000);
        $response = $this->getJson('/api/wallet');
        dump($response->getContent());
        $response->assertStatus(200);
    }
}
