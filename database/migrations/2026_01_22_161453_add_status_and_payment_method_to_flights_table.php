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
        Schema::table('flights', function (Blueprint $table) {
            if (!Schema::hasColumn('flights', 'status')) {
                $table->enum('status', ['pending', 'paid'])->default('pending')->after('beneficiary_type');
            }
            if (!Schema::hasColumn('flights', 'payment_method')) {
                $table->enum('payment_method', ['wallet', 'bank', 'both'])->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            if (Schema::hasColumn('flights', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('flights', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
        });
    }
};
