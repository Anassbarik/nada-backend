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
        Schema::table('accommodations', function (Blueprint $table) {
            // Drop the old single column
            $table->dropColumn('show_flight_prices');
            
            // Add three new columns for granular control
            $table->boolean('show_flight_prices_public')->default(true)->after('status')
                ->comment('Show flight prices to clients on events landing page and flight details page');
            $table->boolean('show_flight_prices_client_dashboard')->default(true)->after('show_flight_prices_public')
                ->comment('Show flight prices to clients in their dashboard for their own bookings');
            $table->boolean('show_flight_prices_organizer_dashboard')->default(true)->after('show_flight_prices_client_dashboard')
                ->comment('Show flight prices to organizers in their dashboard for flights in their events');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            // Drop the three new columns
            $table->dropColumn([
                'show_flight_prices_public',
                'show_flight_prices_client_dashboard',
                'show_flight_prices_organizer_dashboard'
            ]);
            
            // Restore the old single column
            $table->boolean('show_flight_prices')->default(true)->after('status')
                ->comment('Show flight prices to clients in public API');
        });
    }
};
