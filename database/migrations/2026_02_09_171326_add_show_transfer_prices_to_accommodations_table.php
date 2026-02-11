<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            if (!Schema::hasColumn('accommodations', 'show_transfer_prices_public')) {
                $table->boolean('show_transfer_prices_public')->default(true)->after('show_flight_prices_organizer_dashboard');
            }
            if (!Schema::hasColumn('accommodations', 'show_transfer_prices_client_dashboard')) {
                $table->boolean('show_transfer_prices_client_dashboard')->default(true)->after('show_transfer_prices_public');
            }
            if (!Schema::hasColumn('accommodations', 'show_transfer_prices_organizer_dashboard')) {
                $table->boolean('show_transfer_prices_organizer_dashboard')->default(true)->after('show_transfer_prices_client_dashboard');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            if (Schema::hasColumn('accommodations', 'show_transfer_prices_public')) {
                $table->dropColumn('show_transfer_prices_public');
            }
            if (Schema::hasColumn('accommodations', 'show_transfer_prices_client_dashboard')) {
                $table->dropColumn('show_transfer_prices_client_dashboard');
            }
            if (Schema::hasColumn('accommodations', 'show_transfer_prices_organizer_dashboard')) {
                $table->dropColumn('show_transfer_prices_organizer_dashboard');
            }
        });
    }
};
