<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->date('expire_date');
            $table->string('code')->unique();
            $table->integer('quota');
            $table->integer('discount_price')->nullable()->default(0);
            $table->integer('discount_percentage')->nullable()->default(0);
            $table->integer('max_discount')->nullable()->default(0);
            $table->integer('min_price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
