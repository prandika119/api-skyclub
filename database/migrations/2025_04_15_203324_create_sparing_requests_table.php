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
        Schema::create('sparing_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sparing_id')->constrained('sparings');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('status', ['waiting', 'accepted', 'rejected']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sparing_requests');
    }
};
