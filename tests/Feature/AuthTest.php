<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;
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
        $response->assertStatus(422);
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
        $response->assertStatus(422);
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
                    "profile_photo" => null,
                    "wallet" => 0
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
                "profile_photo" => $user->profile_photo,
                'wallet' => $user->wallet->balance
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

    public function testForgotPassword(): void{
        $this->seed([UserSeeder::class]);
        $response = $this->post('/api/users/forgot-password', [
            "email" => "test@gmail.com",
            "no_telp" => "081234567891"
        ]);
        dump("isinya forgot ".$response->getContent());
        $response->assertStatus(200);
    }

    public function testForgotPasswordUsernameWrong(): void{
        $this->seed([UserSeeder::class]);
        $response = $this->post('/api/users/forgot-password', [
            "email" => "test123@gmail.com",
            "no_telp" => "081234567891"
        ]);
        dump("isinya forgot ".$response->getContent());
        $response->assertStatus(400);
    }

    public function testForgotPasswordUsernameEmpty(): void
    {
        $this->seed([UserSeeder::class]);
        $response = $this->post('/api/users/forgot-password', [
            "email" => "",
            "no_telp" => "081234567891"
        ]);
        dump("isinya forgot ".$response->getContent());
        $response->assertStatus(422);
    }

    public function testResetPasswordSuccess(): void
    {
        $this->seed([UserSeeder::class]);
        $user = User::where('email', 'test@gmail.com')->first();
        $token = Password::createToken($user);
        $response = $this->post('/api/users/reset-password', [
            "email" => "test@gmail.com",
            "token" => $token,
            "password" => "newpassword",
            "password_confirmation" => "newpassword"
        ]);
        dump("isinya reset". $response->getContent());
        $response->assertStatus(200);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword', $user->password));
    }

    public function testResetPasswordTokenNotValid(): void
    {
        $this->seed([UserSeeder::class]);
        $user = User::where('email', 'test@gmail.com')->first();
        $token = "token salah";
        $response = $this->post('/api/users/reset-password', [
            "email" => "test@gmail.com",
            "token" => $token,
            "password" => "newpassword",
            "password_confirmation" => "newpassword"
        ]);
        dump("isinya reset". $response->getContent());
        $response->assertStatus(400);
    }

    public function testResetPasswordPasswordNotMatch(): void
    {
        $this->seed([UserSeeder::class]);
        $user = User::where('email', 'test@gmail.com')->first();
        $token = "token salah";
        $response = $this->post('/api/users/reset-password', [
            "email" => "test@gmail.com",
            "token" => $token,
            "password" => "newpassword",
            "password_confirmation" => "password not match"
        ]);
        dump("isinya reset". $response->getContent());
        $response->assertStatus(422);
    }

    public function testLogoutSuccess(): void
    {
        $this->seed([UserSeeder::class]);
        $user = User::where('username', 'test')->first();
        $token = $user->createToken('auth_token')->plainTextToken;
        $response = $this->post('/api/users/logout', [], [
            'Authorization' => 'Bearer '.$token
        ]);
        dump("isinya ini ".$response->getContent());
        $response->assertStatus(200);
    }

    public function testLogoutFailed(): void
    {
        $this->seed([UserSeeder::class]);
        $response = $this->post('/api/users/logout', [], [
            'Authorization' => 'Null'
        ]);
        dump("isinya ini " . $response->getContent());
        $response->assertStatus(401);
    }
}
