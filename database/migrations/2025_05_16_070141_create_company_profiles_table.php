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
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address');
            $table->string('no_telp');
            $table->string('email');
            $table->string('description')->nullable();
            $table->json('payment')->nullable();
            $table->string('banner')->nullable();
            $table->string('logo')->nullable();
            $table->string('slider_1')->nullable();
            $table->string('slider_2')->nullable();
            $table->string('slider_3')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
    }
};
