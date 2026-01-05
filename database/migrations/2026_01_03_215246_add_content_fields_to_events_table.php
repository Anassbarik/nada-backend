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
            $table->text('conditions_content')->nullable()->after('menu_links');
            $table->text('info_content')->nullable()->after('conditions_content');
            $table->text('faq_content')->nullable()->after('info_content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['conditions_content', 'info_content', 'faq_content']);
        });
    }
};
