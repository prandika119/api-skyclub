<?php

namespace Tests\Feature;

use App\Models\Schedule;
use Database\Seeders\FieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CartTest extends TestCase
{

    private function addDataToCart()
    {
        Session::get('cart', []);
        $field = $this->CreateDataField();
        $schedule = new Schedule('28-03-2025', $field);
        $this->post('/api/cart', [
            'field_id' => $schedule->field,
            'schedule_date' => $schedule->date,
            'schedule_time' => '8:00 - 9:00',
            'price' => $schedule->price,
        ]);
    }
    public function testAddCart(): void
    {
        $field = $this->CreateDataField();
        $schedule = new Schedule('28-03-2025', $field);
        $this->AuthUser();
        $response = $this->post('/api/cart', [
            'field_id' => $schedule->field,
            'schedule_date' => $schedule->date,
            'schedule_time' => '8:00 - 9:00',
            'price' => $schedule->price,
        ]);
        dump($response->getContent());
        dump(Session::get('cart'));
        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Cart added successfully',
            'data' => [
                'schedules' => [
                    [
                        'field_id' => $schedule->field,
                        'schedule_date' => $schedule->date,
                        'schedule_time' => '8:00 - 9:00',
                        'price' => $schedule->price,
                    ]
                ],
                'total_price' => $schedule->price
            ]
        ]);
    }

    public function testAddCartTwoTimes()
    {
        $field = $this->CreateDataField();
        $schedule = new Schedule('28-03-2025', $field);
        $this->AuthUser();
        $this->post('/api/cart', [
            'field_id' => $schedule->field,
            'schedule_date' => $schedule->date,
            'schedule_time' => '8:00 - 9:00',
            'price' => $schedule->price,
        ]);
        $response = $this->post('/api/cart', [
            'field_id' => $schedule->field,
            'schedule_date' => $schedule->date,
            'schedule_time' => '10:00 - 11:00',
            'price' => $schedule->price,
        ]);
        dump($response->getContent());
        dump(Session::get('cart'));
        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Cart added successfully',
            'data' => [
                'schedules' => [
                    [
                        'field_id' => $schedule->field,
                        'schedule_date' => $schedule->date,
                        'schedule_time' => '8:00 - 9:00',
                        'price' => $schedule->price,
                    ],
                    [
                        'field_id' => $schedule->field,
                        'schedule_date' => $schedule->date,
                        'schedule_time' => '10:00 - 11:00',
                        'price' => $schedule->price,
                    ]
                ],
                'total_price' => $schedule->price * 2
            ]
        ]);
    }

    public function testGetCart()
    {
        $this->AuthUser();
        $this->addDataToCart();
        $response = $this->get('/api/cart');
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Success',
            'data' => [
                'schedules' => [
                    [
                        'schedule_date' => '28-03-2025',
                        'schedule_time' => '8:00 - 9:00',
                        'price' => 50000,
                    ]
                ],
                'total_price' => 50000
            ]
        ]);
    }

    public function testDeleteCart()
    {
        $this->AuthUser();
        $this->addDataToCart();
        dump(Session::get('cart'));
        $response = $this->delete('/api/cart/0');
        dump($response->getContent());
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Cart deleted successfully',
            'data' => [
                'schedules' => [],
                'total_price' => 0
            ]
        ]);
    }

    public function testDeleteCartNotFound()
    {
        $this->AuthUser();
        $this->addDataToCart();
        dump(Session::get('cart'));
        $response = $this->delete('/api/cart/1');
        dump($response->getContent());
        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Bad Request',
            'errors' => 'Item not found'
        ]);
    }
}
