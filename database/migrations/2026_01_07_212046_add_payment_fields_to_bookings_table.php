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
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('payment_type', ['wallet', 'bank', 'both'])->nullable()->after('price');
            $table->decimal('wallet_amount', 10, 2)->default(0)->after('payment_type');
            $table->decimal('bank_amount', 10, 2)->default(0)->after('wallet_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'wallet_amount', 'bank_amount']);
        });
    }
};
