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
        // Rename packages table to hotel_packages
        Schema::rename('packages', 'hotel_packages');
        
        // Rename price column to total_price
        Schema::table('hotel_packages', function (Blueprint $table) {
            $table->renameColumn('price', 'total_price');
        });
        
        // Add foreign key to bookings table if column exists
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'package_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->foreign('package_id')->references('id')->on('hotel_packages')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key from bookings table
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'package_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropForeign(['package_id']);
            });
        }
        
        Schema::table('hotel_packages', function (Blueprint $table) {
            $table->renameColumn('total_price', 'price');
        });
        
        Schema::rename('hotel_packages', 'packages');
    }
};
