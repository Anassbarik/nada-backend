<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Create wallets for existing users who don't have one.
     */
    public function up(): void
    {
        // Get all users who don't have a wallet
        $usersWithoutWallet = User::whereDoesntHave('wallet')->get();

        foreach ($usersWithoutWallet as $user) {
            $user->wallet()->create(['balance' => 0.00]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally delete wallets if needed
        // DB::table('wallets')->truncate();
    }
};
