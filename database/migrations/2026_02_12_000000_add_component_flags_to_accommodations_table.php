<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->boolean('has_hotel_package')->default(true)->after('commission_percentage');
            $table->boolean('has_flights')->default(true)->after('has_hotel_package');
            $table->boolean('has_transfers')->default(true)->after('has_flights');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropColumn(['has_hotel_package', 'has_flights', 'has_transfers']);
        });
    }
};
