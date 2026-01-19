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
        // Step 1: Drop foreign keys from related tables
        Schema::table('airports', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
        });
        
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
        });
        
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
        });
        
        // Step 2: Rename event_id columns to accommodation_id
        // Using raw SQL for better database compatibility
        DB::statement('ALTER TABLE airports CHANGE event_id accommodation_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE hotels CHANGE event_id accommodation_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE bookings CHANGE event_id accommodation_id BIGINT UNSIGNED NOT NULL');
        
        // Step 3: Remove event_type column (no longer needed as we have separate tables)
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'event_type')) {
                $table->dropColumn('event_type');
            }
        });
        
        // Step 4: Rename events table to accommodations
        Schema::rename('events', 'accommodations');
        
        // Step 5: Recreate foreign keys pointing to accommodations
        Schema::table('airports', function (Blueprint $table) {
            $table->foreign('accommodation_id')->references('id')->on('accommodations')->cascadeOnDelete();
        });
        
        Schema::table('hotels', function (Blueprint $table) {
            $table->foreign('accommodation_id')->references('id')->on('accommodations')->cascadeOnDelete();
        });
        
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('accommodation_id')->references('id')->on('accommodations')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys
        Schema::table('airports', function (Blueprint $table) {
            $table->dropForeign(['accommodation_id']);
        });
        
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropForeign(['accommodation_id']);
        });
        
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['accommodation_id']);
        });
        
        // Rename columns back
        DB::statement('ALTER TABLE airports CHANGE accommodation_id event_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE hotels CHANGE accommodation_id event_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE bookings CHANGE accommodation_id event_id BIGINT UNSIGNED NOT NULL');
        
        // Rename table back
        Schema::rename('accommodations', 'events');
        
        // Recreate foreign keys
        Schema::table('airports', function (Blueprint $table) {
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
        });
        
        Schema::table('hotels', function (Blueprint $table) {
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
        });
        
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
        });
    }
};
