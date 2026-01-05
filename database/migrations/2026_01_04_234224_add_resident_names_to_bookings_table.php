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
            if (!Schema::hasColumn('bookings', 'resident_name_1')) {
                $table->string('resident_name_1')->nullable()->after('special_instructions');
            }
            if (!Schema::hasColumn('bookings', 'resident_name_2')) {
                $table->string('resident_name_2')->nullable()->after('resident_name_1');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'resident_name_1')) {
                $table->dropColumn('resident_name_1');
            }
            if (Schema::hasColumn('bookings', 'resident_name_2')) {
                $table->dropColumn('resident_name_2');
            }
        });
    }
};
