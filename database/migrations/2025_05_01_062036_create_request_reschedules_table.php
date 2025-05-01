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
        Schema::create('request_reschedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('old_list_booking_id')->constrained('list_bookings')->cascadeOnDelete();
            $table->foreignId('new_list_booking_id')->constrained('list_bookings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_reschedules');
    }
};
