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
            $table->json('sections_en')->nullable()->after('sections');
            $table->json('sections_fr')->nullable()->after('sections_en');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_contents', function (Blueprint $table) {
            $table->dropColumn(['sections_en', 'sections_fr']);
        });
    }
};
