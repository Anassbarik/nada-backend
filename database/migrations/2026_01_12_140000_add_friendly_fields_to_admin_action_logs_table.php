<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_action_logs', function (Blueprint $table) {
            $table->string('action_key', 50)->nullable()->after('method')->index();
            $table->string('entity_key', 50)->nullable()->after('action_key')->index();
            $table->string('target_label', 191)->nullable()->after('subject_id')->index();
            $table->string('outcome', 20)->nullable()->after('status_code')->index(); // success|failed
            $table->text('details')->nullable()->after('outcome');
        });
    }

    public function down(): void
    {
        Schema::table('admin_action_logs', function (Blueprint $table) {
            $table->dropColumn(['action_key', 'entity_key', 'target_label', 'outcome', 'details']);
        });
    }
};


