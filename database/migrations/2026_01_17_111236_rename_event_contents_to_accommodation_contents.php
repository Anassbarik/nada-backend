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
        // Step 1: Drop foreign key and unique constraint
        Schema::table('event_contents', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropUnique(['event_id', 'page_type']);
        });
        
        // Step 2: Rename event_id to accommodation_id
        // Using raw SQL for better database compatibility
        DB::statement('ALTER TABLE event_contents CHANGE event_id accommodation_id BIGINT UNSIGNED NOT NULL');
        
        // Step 3: Rename table
        Schema::rename('event_contents', 'accommodation_contents');
        
        // Step 4: Recreate foreign key and unique constraint
        Schema::table('accommodation_contents', function (Blueprint $table) {
            $table->foreign('accommodation_id')->references('id')->on('accommodations')->cascadeOnDelete();
            $table->unique(['accommodation_id', 'page_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key and unique constraint
        Schema::table('accommodation_contents', function (Blueprint $table) {
            $table->dropForeign(['accommodation_id']);
            $table->dropUnique(['accommodation_id', 'page_type']);
        });
        
        // Rename column back
        DB::statement('ALTER TABLE accommodation_contents CHANGE accommodation_id event_id BIGINT UNSIGNED NOT NULL');
        
        // Rename table back
        Schema::rename('accommodation_contents', 'event_contents');
        
        // Recreate foreign key and unique constraint
        Schema::table('event_contents', function (Blueprint $table) {
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
            $table->unique(['event_id', 'page_type']);
        });
    }
};
