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
        // First, modify the role column to include super-admin
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user', 'admin', 'super-admin') DEFAULT 'user'");

        // Then, update existing admin users to super-admin
        DB::table('users')->where('role', 'admin')->update(['role' => 'super-admin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert super-admin back to admin
        DB::table('users')->where('role', 'super-admin')->update(['role' => 'admin']);

        // Revert role column to original enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user', 'admin') DEFAULT 'user'");
    }
};
