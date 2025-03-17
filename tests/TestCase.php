<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('delete from users');
        $directory = storage_path('app/public/profile_photos');
        File::cleanDirectory($directory);
    }

    protected function AuthUser(): User
    {
        $this->seed([UserSeeder::class]);
        $user = User::where('username', 'test')->first();
        $this->actingAs($user);
        return $user;
    }
}
