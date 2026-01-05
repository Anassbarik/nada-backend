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
        Schema::table('event_contents', function (Blueprint $table) {
            // Add content field as longText
            if (!Schema::hasColumn('event_contents', 'content')) {
                $table->longText('content')->nullable()->after('sections');
            }
            
            // Add type field if it doesn't exist (as alias for page_type)
            // We'll use page_type as the actual column, but map it to 'type' in API
            // Or add a new type column if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_contents', function (Blueprint $table) {
            if (Schema::hasColumn('event_contents', 'content')) {
                $table->dropColumn('content');
            }
        });
    }
};
