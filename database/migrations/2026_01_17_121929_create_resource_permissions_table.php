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
        Schema::create('resource_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('resource_type'); // 'event', 'hotel', 'package'
            $table->unsignedBigInteger('resource_id'); // ID of the event, hotel, or package
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Admin who has sub-permission
            $table->timestamps();

            // Ensure a user can't have duplicate permissions on the same resource
            $table->unique(['resource_type', 'resource_id', 'user_id']);
            
            // Index for efficient lookups
            $table->index(['resource_type', 'resource_id']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_permissions');
    }
};
