<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix existing admin user's role to 'admin'
        DB::table('users')
            ->where('email', 'admin@example.com')
            ->update(['role' => 'admin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert admin user's role to 'user' (if needed)
        DB::table('users')
            ->where('email', 'admin@example.com')
            ->update(['role' => 'user']);
    }
};

