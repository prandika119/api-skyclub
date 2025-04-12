<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Voucher::insert([
            [
                'code' => 'DISCOUNT10',
                'expire_date' => Carbon::now()->addDays(10),
                'quota' => 100,
                'discount_price' => 0,
                'discount_percentage' => 10,
                'max_discount' => 50000,
                'min_price' => 100000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'FLAT50',
                'expire_date' => Carbon::now()->addDays(5),
                'quota' => 50,
                'discount_price' => 50000,
                'discount_percentage' => 0,
                'max_discount' => 0,
                'min_price' => 200000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'NOQUOTA',
                'expire_date' => Carbon::now()->addDays(5),
                'quota' => 0,
                'discount_price' => 50000,
                'discount_percentage' => 0,
                'max_discount' => 0,
                'min_price' => 200000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'EXPIREDVOUCHER',
                'expire_date' => Carbon::now()->subDays(1),
                'quota' => 10,
                'discount_price' => 10000,
                'discount_percentage' => 0,
                'max_discount' => 0,
                'min_price' => 50000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
