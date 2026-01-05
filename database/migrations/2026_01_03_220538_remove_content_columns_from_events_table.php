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
            if (Schema::hasColumn('events', 'conditions_content')) {
                $table->dropColumn('conditions_content');
            }
            if (Schema::hasColumn('events', 'info_content')) {
                $table->dropColumn('info_content');
            }
            if (Schema::hasColumn('events', 'faq_content')) {
                $table->dropColumn('faq_content');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->text('conditions_content')->nullable()->after('menu_links');
            $table->text('info_content')->nullable()->after('conditions_content');
            $table->text('faq_content')->nullable()->after('info_content');
        });
    }
};
