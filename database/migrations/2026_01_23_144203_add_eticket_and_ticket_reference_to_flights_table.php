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
        Schema::table('flights', function (Blueprint $table) {
            $table->string('eticket')->nullable()->after('eticket_path')->comment('eTicket number/flight number entered by admin');
            $table->string('ticket_reference')->nullable()->after('eticket')->comment('Ticket reference from airline company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flights', function (Blueprint $table) {
            $table->dropColumn(['eticket', 'ticket_reference']);
        });
    }
};
