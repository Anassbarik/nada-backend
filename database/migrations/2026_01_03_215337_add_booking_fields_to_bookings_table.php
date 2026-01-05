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
            // Add package_id if it doesn't exist
            if (!Schema::hasColumn('bookings', 'package_id')) {
                $table->foreignId('package_id')->nullable()->after('hotel_id')->constrained('hotel_packages')->nullOnDelete();
            }
            
            // Add new fields
            if (!Schema::hasColumn('bookings', 'guest_phone')) {
                $table->string('guest_phone')->nullable()->after('guest_email');
            }
            if (!Schema::hasColumn('bookings', 'special_requests')) {
                $table->text('special_requests')->nullable()->after('guest_phone');
            }
            if (!Schema::hasColumn('bookings', 'booking_reference')) {
                $table->string('booking_reference')->unique()->nullable()->after('special_requests');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'guest_phone')) {
                $table->dropColumn('guest_phone');
            }
            if (Schema::hasColumn('bookings', 'special_requests')) {
                $table->dropColumn('special_requests');
            }
            if (Schema::hasColumn('bookings', 'booking_reference')) {
                $table->dropColumn('booking_reference');
            }
        });
    }
};
