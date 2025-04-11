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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->date('order_date');
            $table->enum('status', ['accepted', 'pending', 'canceled'])->default('pending');
            $table->foreignId('rented_by')->constrained('users')->onUpdate('restrict')->onDelete('restrict');
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('user_offline')->nullable()->constrained('user_offlines')->onUpdate('restrict')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
