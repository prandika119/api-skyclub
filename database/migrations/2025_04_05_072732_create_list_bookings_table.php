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
        Schema::create('list_bookings', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('session');
            $table->bigInteger('price');
            $table->foreignId('field_id')->constrained('fields')->onUpdate('restrict')->onDelete('restrict');
            $table->foreignId('booking_id')->constrained('bookings')->onUpdate('restrict')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('list_bookings');
    }
};
