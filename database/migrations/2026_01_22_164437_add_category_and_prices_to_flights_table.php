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
            if (!Schema::hasColumn('flights', 'flight_category')) {
                $table->enum('flight_category', ['one_way', 'round_trip'])->default('one_way')->after('flight_class');
            }
            if (!Schema::hasColumn('flights', 'departure_price_ttc')) {
                $table->decimal('departure_price_ttc', 10, 2)->default(0)->after('departure_flight_number');
            }
            if (!Schema::hasColumn('flights', 'return_price_ttc')) {
                $table->decimal('return_price_ttc', 10, 2)->nullable()->after('return_flight_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            if (Schema::hasColumn('flights', 'flight_category')) {
                $table->dropColumn('flight_category');
            }
            if (Schema::hasColumn('flights', 'departure_price_ttc')) {
                $table->dropColumn('departure_price_ttc');
            }
            if (Schema::hasColumn('flights', 'return_price_ttc')) {
                $table->dropColumn('return_price_ttc');
            }
        });
    }
};
