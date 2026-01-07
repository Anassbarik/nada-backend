<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Maximum number of tokens allowed per user.
     * When limit is reached, oldest tokens are automatically deleted.
     */
    private const MAX_TOKENS_PER_USER = 5;

    /**
     * Register a new user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', // At least one lowercase, one uppercase, one number
            ],
        ], [
            'name.required' => __('Le nom est obligatoire.'),
            'email.required' => __('L\'email est obligatoire.'),
            'email.email' => __('L\'email doit être une adresse email valide.'),
            'email.unique' => __('Cet email est déjà utilisé.'),
            'password.required' => __('Le mot de passe est obligatoire.'),
            'password.confirmed' => __('La confirmation du mot de passe ne correspond pas.'),
            'password.min' => __('Le mot de passe doit contenir au moins 8 caractères.'),
            'password.regex' => __('Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre.'),
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user', // Default role for API registrations
        ]);

        // Create wallet automatically on user registration
        $user->wallet()->create(['balance' => 0.00]);

        // Create token and enforce maximum token limit
        $token = $this->createTokenWithLimit($user, 'booking-app');

        // Load wallet for response
        $user->load('wallet');

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'wallet' => [
                    'id' => $user->wallet->id,
                    'balance' => number_format((float)$user->wallet->balance, 2, '.', ''),
                    'balance_formatted' => number_format((float)$user->wallet->balance, 2, ',', ' ') . ' €',
                ],
            ],
        ], 201);
    }

    /**
     * Login user and create token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('Les identifiants fournis sont incorrects.')],
            ]);
        }

        // Ensure wallet exists (for legacy users who might not have one)
        if (!$user->wallet) {
            $user->wallet()->create(['balance' => 0.00]);
            $user->refresh();
        }

        // Create token and enforce maximum token limit
        // Oldest tokens are automatically deleted when limit is reached
        $token = $this->createTokenWithLimit($user, 'booking-app');

        // Load wallet for response
        $user->load('wallet');

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'wallet' => [
                    'id' => $user->wallet->id,
                    'balance' => number_format((float)$user->wallet->balance, 2, '.', ''),
                    'balance_formatted' => number_format((float)$user->wallet->balance, 2, ',', ' ') . ' €',
                ],
            ],
        ]);
    }

    /**
     * Logout user (revoke token).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => __('Logged out successfully.'),
        ]);
    }

    /**
     * Get authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        $user = $request->user();
        
        // Ensure wallet exists (for legacy users who might not have one)
        if (!$user->wallet) {
            $user->wallet()->create(['balance' => 0.00]);
            $user->refresh();
        }

        $user->load('wallet');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'wallet' => [
                    'id' => $user->wallet->id,
                    'balance' => number_format((float)$user->wallet->balance, 2, '.', ''),
                    'balance_formatted' => number_format((float)$user->wallet->balance, 2, ',', ' ') . ' €',
                ],
            ],
        ]);
    }

    /**
     * Create a token with automatic cleanup of old tokens when limit is reached.
     * 
     * @param  \App\Models\User  $user
     * @param  string  $tokenName
     * @return string
     */
    private function createTokenWithLimit(User $user, string $tokenName): string
    {
        // Get current token count
        $tokenCount = $user->tokens()->count();

        // If at or above limit, delete oldest tokens (keep most recent)
        if ($tokenCount >= self::MAX_TOKENS_PER_USER) {
            $tokensToDelete = $tokenCount - self::MAX_TOKENS_PER_USER + 1; // +1 for the new token we're creating
            
            // Delete oldest tokens (ordered by created_at ascending)
            $user->tokens()
                ->orderBy('created_at', 'asc')
                ->limit($tokensToDelete)
                ->delete();
        }

        // Create new token
        return $user->createToken($tokenName)->plainTextToken;
    }
}
