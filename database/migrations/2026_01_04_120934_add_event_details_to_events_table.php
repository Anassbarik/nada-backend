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
            $table->string('venue')->nullable()->after('name');
            $table->date('start_date')->nullable()->after('venue');
            $table->date('end_date')->nullable()->after('start_date');
            $table->string('website_url')->nullable()->after('end_date');
            $table->string('organizer_logo')->nullable()->after('website_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['venue', 'start_date', 'end_date', 'website_url', 'organizer_logo']);
        });
    }
};
