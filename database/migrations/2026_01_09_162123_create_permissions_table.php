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
        Schema::dropIfExists('permissions');
        
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('resource', 50); // e.g., 'events', 'hotels', 'packages', 'partners', 'bookings', 'invoices'
            $table->string('action', 50); // e.g., 'view', 'create', 'edit', 'delete'
            $table->string('name'); // Human-readable name, e.g., 'View Events'
            $table->text('description')->nullable();
            $table->timestamps();

            // Ensure unique combination of resource and action
            $table->unique(['resource', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
