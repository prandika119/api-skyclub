<?php

namespace Tests\Feature;

use App\Models\Sparing;
use App\Models\SparingRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SparingTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testCreateSparing(): void
    {
        $user = $this->AuthUser();
        $user->update(['team' => 'test FC']);
        $date = Carbon::now()->addDays(7);
        $list_booking = $this->paymentBooking($user, $date);
        $response = $this->post('/api/sparings', [
            'list_booking_id' => $list_booking->id,
            'description' => 'Sparing Test'
        ]);
        dump($response->getContent());
        $response->assertStatus(201);
        $this->assertDatabaseHas('sparings', [
            'list_booking_id' => $list_booking->id,
            'description' => 'Sparing Test',
            'status' => 'waiting'
        ]);
    }

    public function testCreateSparingWithoutTeam(): void
    {
        $user = $this->AuthUser();
        $date = Carbon::now()->addDays(7);
        $list_booking = $this->paymentBooking($user, $date);
        $response = $this->post('/api/sparings', [
            'list_booking_id' => $list_booking->id,
            'description' => 'Sparing Test'
        ]);
        dump($response->getContent());
        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Bad Request',
            'errors' => 'Silahkan isi team terlebih dahulu'
        ]);
    }

    public function testGetAllSparing(): void
    {
        $user = $this->AuthUser();
        $date1 = Carbon::now()->addDays(7);
        $date2 = Carbon::now()->addDays(3);
        $this->createSparing($user, $date1);
        $this->createSparing($user, $date2);
        $response = $this->get('/api/sparings');
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Sukses',
            'data' => [
                [
                    'description' => 'Sparing Test',
                    'status' => 'waiting'
                ],
                [
                    'description' => 'Sparing Test',
                    'status' => 'waiting'
                ]
            ]
        ]);
    }

    public function testGetSparingNotAvailable(): void
    {
        $response = $this->get('/api/sparings');
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Tidak ada sparing yang tersedia',
            'data' => []
        ]);
    }

    public function testAddSparingRequestSuccess()
    {
        $user2 = $this->AuthUser2();
        $user = $this->AuthUser();
        $date = Carbon::now()->addDays(7);
        $user->update(['team' => 'Test FC']);
        $sparing = $this->createSparing($user2, $date);

        $response = $this->post('/api/sparings/' . $sparing->id . '/request');
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Sukses']);
        $this->assertDatabaseHas('sparing_requests', [
            'user_id' => $user->id,
            'sparing_id' => $sparing->id,
            'status' => 'waiting',
        ]);
        dump($response->getContent());
    }

    public function testAddSparingRequestWithoutTeam()
    {
        $user2 = $this->AuthUser2();
        $user = $this->AuthUser();
        $date = Carbon::now()->addDays(7);
        $sparing = $this->createSparing($user2, $date);

        $response = $this->post('/api/sparings/' . $sparing->id . '/request');

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Bad Request',
            'errors' => 'Silahkan isi team terlebih dahulu',
        ]);
        $this->assertDatabaseMissing('sparing_requests', [
            'user_id' => $user->id,
            'sparing_id' => $sparing->id,
            'status' => 'waiting',
        ]);
        dump($response->getContent());
    }

    public function testAddSparingRequestAlreadyRequested()
    {
        $user2 = $this->AuthUser2();
        $user = $this->AuthUser();
        $date = Carbon::now()->addDays(7);
        $user->update(['team' => 'Test FC']);
        $sparing = $this->createSparing($user2, $date);
        DB::table('sparing_requests')->insert([
            'user_id' => $user->id,
            'sparing_id' => $sparing->id,
            'status' => 'waiting',
        ]);

        $response = $this->post('/api/sparings/' . $sparing->id . '/request');
        dump($response->getContent());
        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Bad Request',
            'errors' => 'Sparing sudah diminta',
        ]);
    }

    public function testAddSparingRequestAlreadyDone()
    {
        $user2 = $this->AuthUser2();
        $user = $this->AuthUser();
        $date = Carbon::now()->addDays(7);
        $user->update(['team' => 'Test FC']);
        $sparing = $this->createSparing($user2, $date);
        $sparing->update(['status' => 'done']);

        $response = $this->post('/api/sparings/' . $sparing->id . '/request');
        dump($response->getContent());
        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Bad Request',
            'errors' => 'Sparing sudah ditutup',
        ]);
    }

    public function testAddSparingRequestOwnSparing()
    {
        $user = $this->AuthUser();
        $date = Carbon::now()->addDays(7);
        $user->update(['team' => 'Test FC']);
        $sparing = $this->createSparing($user, $date);;

        $response = $this->post('/api/sparings/' . $sparing->id . '/request');
        dump($response->getContent());
        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Bad Request',
            'errors' => 'Anda tidak bisa meminta sparing milik sendiri',
        ]);
    }

    public function testAcceptSparingRequestSuccess()
    {
        $user2 = $this->AuthUser2();
        $user = $this->AuthUser();
        $admin = User::where('role', 'admin')->first();
        $date = Carbon::now()->addDays(7);
        $user->update(['team' => 'Test FC']);
        $sparing = $this->createSparing($user, $date);
        $sparingRequestAdmin = SparingRequest::create([
            'user_id' => $admin->id,
            'sparing_id' => $sparing->id,
            'status' => 'waiting',
        ]);
        $sparingRequest = SparingRequest::create([
            'user_id' => $user2->id,
            'sparing_id' => $sparing->id,
            'status' => 'waiting',
        ]);

        $response = $this->post('/api/sparings/' . $sparingRequest->id . '/accept');
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Sukses',
        ]);
        $this->assertDatabaseHas('sparing_requests', [
            'id' => $sparingRequest->id,
            'status' => 'accepted',
        ]);
        $this->assertDatabaseHas('sparing_requests', [
            'id' => $sparingRequestAdmin->id,
            'status' => 'rejected',
        ]);
        $this->assertDatabaseHas('sparings', [
            'id' => $sparing->id,
            'status' => 'done',
        ]);
    }

    public function testAcceptSparingRequestUnauthorized()
    {
        $user2 = $this->AuthUser2();
        $user = $this->AuthUser();
        $date = Carbon::now()->addDays(7);
        $user->update(['team' => 'Test FC']);
        $sparing = $this->createSparing($user2, $date);

        $sparingRequest = SparingRequest::create([
            'user_id' => $user->id,
            'sparing_id' => $sparing->id,
            'status' => 'waiting',
        ]);

        $response = $this->post('/api/sparings/' . $sparingRequest->id . '/accept'  );
        dump($response->getContent());
        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Bad Request',
            'errors' => 'Anda tidak bisa menerima sparing milik orang lain',
        ]);
    }

    public function testAcceptSparingRequestAlreadyDone()
    {
        $user2 = $this->AuthUser2();
        $user = $this->AuthUser();
        $date = Carbon::now()->addDays(7);
        $user->update(['team' => 'Test FC']);
        $sparing = $this->createSparing($user, $date);

        $sparingRequest = SparingRequest::create([
            'user_id' => $user2->id,
            'sparing_id' => $sparing->id,
            'status' => 'waiting',
        ]);

        $sparing->update(['status' => 'done']);

        $response = $this->post('/api/sparings/' . $sparingRequest->id . '/accept');

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Bad Request',
            'errors' => 'Sparing sudah ditutup',
        ]);
    }

    public function testRejectSparingRequestSuccess()
    {
        $user2 = $this->AuthUser2();
        $user = $this->AuthUser();
        $date = Carbon::now()->addDays(7);
        $user->update(['team' => 'Test FC']);
        $sparing = $this->createSparing($user, $date);

        $sparingRequest = SparingRequest::create([
            'user_id' => $user2->id,
            'sparing_id' => $sparing->id,
            'status' => 'waiting',
        ]);

        $response = $this->post('/api/sparings/' . $sparingRequest->id . '/reject');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Sukses']);
        $this->assertDatabaseHas('sparing_requests', [
            'id' => $sparingRequest->id,
            'status' => 'rejected',
        ]);
    }

    public function testRejectSparingRequestUnauthorized()
    {
        $user2 = $this->AuthUser2();
        $user = $this->AuthUser();
        $date = Carbon::now()->addDays(7);
        $user->update(['team' => 'Test FC']);
        $sparing = $this->createSparing($user2, $date);

        $sparingRequest = SparingRequest::create([
            'user_id' => $user->id,
            'sparing_id' => $sparing->id,
            'status' => 'waiting',
        ]);

        $response = $this->post('/api/sparings/' . $sparingRequest->id . '/reject');
        dump($response->getContent());
        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Bad Request',
            'errors' => 'Anda tidak bisa menolak sparing milik orang lain',
        ]);
    }

    public function testRejectSparingRequestAlreadyDone()
    {
        $user2 = $this->AuthUser2();
        $user = $this->AuthUser();
        $date = Carbon::now()->addDays(7);
        $user->update(['team' => 'Test FC']);
        $sparing = $this->createSparing($user, $date);

        $sparingRequest = SparingRequest::create([
            'user_id' => $user2->id,
            'sparing_id' => $sparing->id,
            'status' => 'waiting',
        ]);

        $sparing->update(['status' => 'done']);

        $response = $this->post('/api/sparings/' . $sparingRequest->id . '/reject');

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Bad Request',
            'errors' => 'Sparing sudah ditutup',
        ]);
    }

}
