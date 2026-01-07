<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
                    'balance_formatted' => number_format((float)$user->wallet->balance, 2, ',', ' ') . ' €',
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
     * Update user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', // At least one lowercase, one uppercase, one number
            ],
        ], [
            'current_password.required' => __('Le mot de passe actuel est obligatoire.'),
            'password.required' => __('Le nouveau mot de passe est obligatoire.'),
            'password.confirmed' => __('La confirmation du mot de passe ne correspond pas.'),
            'password.min' => __('Le mot de passe doit contenir au moins 8 caractères.'),
            'password.regex' => __('Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre.'),
        ]);

        $user = $request->user();

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('Le mot de passe actuel est incorrect.')],
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Mot de passe mis à jour avec succès.'),
        ]);
    }
}
