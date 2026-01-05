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
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'location')) {
                $table->string('location')->nullable()->after('venue');
            }
            if (!Schema::hasColumn('events', 'google_maps_url')) {
                $table->string('google_maps_url', 500)->nullable()->after('location');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'location')) {
                $table->dropColumn('location');
            }
            if (Schema::hasColumn('events', 'google_maps_url')) {
                $table->dropColumn('google_maps_url');
            }
        });
    }
};
