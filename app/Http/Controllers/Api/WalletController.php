<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Get authenticated user's wallet.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Ensure wallet exists (for legacy users who might not have one)
        if (!$user->wallet) {
            $user->wallet()->create(['balance' => 0.00]);
            $user->refresh();
        }

        $user->load('wallet');

        return response()->json([
            'success' => true,
            'data' => [
                'wallet' => [
                    'id' => $user->wallet->id,
                    'balance' => number_format((float)$user->wallet->balance, 2, '.', ''),
                    'balance_formatted' => number_format((float)$user->wallet->balance, 2, ',', ' ') . ' MAD',
                    'user_id' => $user->id,
                ],
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
        ]);
    }

    /**
     * Get wallet balance (simplified endpoint).
     * Route: GET /api/wallet/balance
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function balance(Request $request)
    {
        $user = $request->user();
        
        // Ensure wallet exists
        if (!$user->wallet) {
            $user->wallet()->create(['balance' => 0.00]);
            $user->refresh();
        }

        $user->load('wallet');

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => number_format((float)$user->wallet->balance, 2, '.', ''),
                'balance_formatted' => number_format((float)$user->wallet->balance, 2, ',', ' ') . ' MAD',
            ],
        ]);
    }
}
