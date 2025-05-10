<?php

namespace Tests\Feature;

use App\Models\Field;
use Database\Seeders\FieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testGetSchedules(): void
    {
        $this->seed([FieldSeeder::class]);
        $field = Field::where('name', 'fieldTest')->first();
        $response = $this->get('/api/fields/'.$field->id.'/schedules?start_date=2025-04-07&end_date=2025-04-13');
        dump($response);
        dump(json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT));

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Success',
            'data' => []
        ]);
    }

    public function testGetSchedulesWithBooking(): void
    {
        $user = $this->AuthUser();
        $booking = $this->paymentBooking($user, '2025-05-8');
        $field = Field::where('name', 'fieldTest')->first();
        $response = $this->get('/api/fields/'.$field->id.'/schedules');
        dump(json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT));
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Success',
            'data' => []
        ]);
        $response->assertJsonFragment([
            'is_available' => false,
        ]);
    }
}
