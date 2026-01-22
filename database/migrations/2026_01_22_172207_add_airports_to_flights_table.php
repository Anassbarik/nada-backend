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
            if (!Schema::hasColumn('flights', 'departure_airport')) {
                $table->string('departure_airport', 100)->nullable()->after('departure_flight_number');
            }
            if (!Schema::hasColumn('flights', 'arrival_airport')) {
                $table->string('arrival_airport', 100)->nullable()->after('arrival_time');
            }
            if (!Schema::hasColumn('flights', 'return_departure_airport')) {
                $table->string('return_departure_airport', 100)->nullable()->after('return_flight_number');
            }
            if (!Schema::hasColumn('flights', 'return_arrival_airport')) {
                $table->string('return_arrival_airport', 100)->nullable()->after('return_arrival_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            if (Schema::hasColumn('flights', 'departure_airport')) {
                $table->dropColumn('departure_airport');
            }
            if (Schema::hasColumn('flights', 'arrival_airport')) {
                $table->dropColumn('arrival_airport');
            }
            if (Schema::hasColumn('flights', 'return_departure_airport')) {
                $table->dropColumn('return_departure_airport');
            }
            if (Schema::hasColumn('flights', 'return_arrival_airport')) {
                $table->dropColumn('return_arrival_airport');
            }
        });
    }
};
