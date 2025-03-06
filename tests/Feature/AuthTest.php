<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AuthTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    public function testRegisterSuccess(): void
    {
        $response = $this->post('/api/users',
        [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'johndoe@gmail.com',
            'no_telp' => '081234567890',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'User created successfully'
        ]);
        dump("isinya ini".$response->getContent());
    }

    public function testRegisterEmailNull(): void
    {
        $response = $this->post('/api/users',
            [
                'name' => 'John Doe',
                'username' => 'johndoe',
                'email' => '',
                'no_telp' => '081234567890',
                'password' => 'password',
                'password_confirmation' => 'password'
            ]);
        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Validation error',
            "errors" => [

            ]
        ]);

        dump("isinya ini".$response->getContent());
    }

    public function testRegisterUsernameDuplicate(): void
    {
        User::create([
            'name' => 'Existing Username',
            'username' => 'johndoe',
            'email' => 'existinguser@gmail.com',
            'no_telp' => '081234567891',
            'password' => bcrypt('password')
        ]);
        $response = $this->post('/api/users',
            [
                'name' => 'John Doe',
                'username' => 'johndoe',
                'email' => '',
                'no_telp' => '081234567890',
                'password' => 'password',
                'password_confirmation' => 'password'
            ]);
        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Validation error',
            "errors" => [

            ]
        ]);
        dump("isinya ini".$response->getContent());
    }
}
