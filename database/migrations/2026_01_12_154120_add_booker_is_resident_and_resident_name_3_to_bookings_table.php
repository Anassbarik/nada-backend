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
            if (!Schema::hasColumn('bookings', 'booker_is_resident')) {
                $table->boolean('booker_is_resident')->default(true)->after('resident_name_2');
            }
            if (!Schema::hasColumn('bookings', 'resident_name_3')) {
                $table->string('resident_name_3')->nullable()->after('resident_name_2');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'booker_is_resident')) {
                $table->dropColumn('booker_is_resident');
            }
            if (Schema::hasColumn('bookings', 'resident_name_3')) {
                $table->dropColumn('resident_name_3');
            }
        });
    }
};
