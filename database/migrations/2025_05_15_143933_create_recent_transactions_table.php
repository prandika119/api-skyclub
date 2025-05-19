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
        Schema::create('recent_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('recipient_id')->nullable()->constrained('users')->onDelete('restrict');
            $table->foreignId('wallet_id')->constrained()->onDelete('restrict');
            $table->enum('transaction_type', ['topup', 'transfer', 'withdraw', 'booking']); // 'topup' or 'transfer'
            $table->integer('amount');
            $table->string('bank_ewallet')->nullable(); // Bank or e-wallet name
            $table->string('number')->nullable(); // Account number or e-wallet number
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recent_transactions');
    }
};
