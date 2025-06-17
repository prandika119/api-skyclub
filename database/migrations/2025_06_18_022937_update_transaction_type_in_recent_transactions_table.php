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
        Schema::table('recent_transactions', function (Blueprint $table) {
            Schema::table('recent_transactions', function (Blueprint $table) {
                $table->enum('transaction_type', ['booking', 'refund', 'topup', 'transfer', 'withdraw'])->change();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recent_transactions', function (Blueprint $table) {
            $table->enum('transaction_type', ['booking', 'topup', 'withdraw', 'transfer'])->change();
        });
    }
};
