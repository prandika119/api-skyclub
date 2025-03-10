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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('username')->unique();
            $table->string('no_telp')->unique();
            $table->string('team')->nullable();
            $table->text('address')->nullable();
            $table->enum('role', ['penyewa', 'admin'])->default('penyewa');
            $table->date('date_of_birth')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('password');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
