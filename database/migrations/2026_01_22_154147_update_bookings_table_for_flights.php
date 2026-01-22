<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Add flight_id
            if (!Schema::hasColumn('bookings', 'flight_id')) {
                $table->foreignId('flight_id')->nullable()->after('package_id')->constrained('flights')->onDelete('set null');
            }
            
            // Make hotel_id and package_id nullable
            if (Schema::hasColumn('bookings', 'hotel_id')) {
                $table->foreignId('hotel_id')->nullable()->change();
            }
            if (Schema::hasColumn('bookings', 'package_id')) {
                $table->foreignId('package_id')->nullable()->change();
            }
            
            // Make flight_number, flight_date, flight_time nullable (they're for different scenario)
            if (Schema::hasColumn('bookings', 'flight_number')) {
                $table->string('flight_number')->nullable()->change();
            }
            if (Schema::hasColumn('bookings', 'flight_date')) {
                $table->date('flight_date')->nullable()->change();
            }
            if (Schema::hasColumn('bookings', 'flight_time')) {
                $table->time('flight_time')->nullable()->change();
            }
            
            // Add unique index on booking_reference if it doesn't exist
            if (Schema::hasColumn('bookings', 'booking_reference')) {
                $indexExists = false;
                try {
                    $indexes = DB::select("SHOW INDEX FROM bookings WHERE Key_name = 'bookings_booking_reference_unique'");
                    $indexExists = count($indexes) > 0;
                } catch (\Exception $e) {
                    // If query fails, assume index doesn't exist
                }
                
                if (!$indexExists) {
                    $table->unique('booking_reference', 'bookings_booking_reference_unique');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'flight_id')) {
                $table->dropForeign(['flight_id']);
                $table->dropColumn('flight_id');
            }
            
            // Note: We don't revert nullable changes as they might break existing data
            // If needed, these should be handled manually
        });
    }
};
