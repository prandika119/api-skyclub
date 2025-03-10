<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
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
        $user = $this->seed([UserSeeder::class]);
        $response = $this->post('/api/users',
            [
                'name' => 'John Doe',
                'username' => 'test',
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

    public function testLoginSuccess(): void
    {
        $this->seed([UserSeeder::class]);
        $response = $this->post('/api/users/login',
            [
                'username' => 'test',
                'password' => 'password',
            ]);
        dump("isinya ini".$response->getContent());
        $response->assertStatus(200);
        $response->assertJson([
            "message" => "Login Success",
            "data" => [
                "user" => [
                    "name" => "test",
                    "profile_photo" => null
                ]
            ]
        ]);
    }

    public function testLoginWrongCredentials(): void
    {
        $this->seed([UserSeeder::class]);
        $response = $this->post('/api/users/login',
            [
                'username' => 'test',
                'password' => 'password123',
            ]);
        dump("isinya ini".$response->getContent());
        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthorize',
            "errors" => ["message" => "Username or Password Wrong"]
        ]);
    }

    public function testLoginUsernameNotFound(): void
    {
        $response = $this->post('/api/users/login',
            [
                'username' => 'test123',
                'password' => 'password',
            ]);
        dump("isinya ini".$response->getContent());
        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthorize',
            "errors" => ["message" => "Username or Password Wrong"]
        ]);
    }

    public function testCurrentUserSuccess(): void{
        $this->seed([UserSeeder::class]);
        $user = User::where('username', 'test')->first();
        $token = $user->createToken('auth_token')->plainTextToken;
        $response = $this->get('/api/users/current', [
            'Authorization' => 'Bearer '.$token
        ]);
        dump("isinya ini ".$response->getContent());
        $response->assertStatus(200);
        $response->assertJson([
            "message" => "Success Get This User",
            "data" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "no_telp" => $user->no_telp,
                "tema" => $user->team,
                "address" => $user->address,
                "date_of_birth" => $user->date_of_birth,
                "profile_photo" => $user->profile_photo
            ]
        ]);
    }

    public function testCurrentUserFailed(): void{
        $this->seed([UserSeeder::class]);
        $user = User::where('username', 'test')->first();
        $response = $this->get('/api/users/current', [
            'Authorization' => 'Bearer '
        ]);
        dump("isinya ini ".$response->getContent());
        $response->assertStatus(401);
    }
}
