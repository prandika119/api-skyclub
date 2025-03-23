<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'test',
            'username' => 'test',
            'email' => 'test@gmail.com',
            'no_telp' => '081234567891',
            'password' => bcrypt('password')
        ]);

        User::create([
            'name' => 'admin',
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'no_telp' => '081234567892',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);
    }
}
