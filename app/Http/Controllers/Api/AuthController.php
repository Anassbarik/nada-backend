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
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => __('Le nom est obligatoire.'),
            'email.required' => __('L\'email est obligatoire.'),
            'email.email' => __('L\'email doit être une adresse email valide.'),
            'email.unique' => __('Cet email est déjà utilisé.'),
            'password.required' => __('Le mot de passe est obligatoire.'),
            'password.confirmed' => __('La confirmation du mot de passe ne correspond pas.'),
            'password.min' => __('Le mot de passe doit contenir au moins 8 caractères.'),
            'password.regex' => __('Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre.'),
            'phone.max' => __('Le numéro de téléphone ne doit pas dépasser 50 caractères.'),
            'company.max' => __('Le nom de l\'entreprise ne doit pas dépasser 255 caractères.'),
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'company' => $validated['company'] ?? null,
            'role' => 'user', // Default role for API registrations
        ]);

        // Create wallet automatically on user registration
        $user->wallet()->create(['balance' => 0.00]);

        // Create token and enforce maximum token limit
        $token = $this->createTokenWithLimit($user, 'booking-app');

        // Load wallet for response
        $user->load('wallet');

        return response()->json([
            'success' => true,
            'message' => __('Compte créé avec succès.'),
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'company' => $user->company,
                    'role' => $user->role ?? 'user',
                    'email_verified_at' => $user->email_verified_at?->toISOString(),
                    'wallet' => [
                        'id' => $user->wallet->id,
                        'balance' => number_format((float)$user->wallet->balance, 2, '.', ''),
                        'balance_formatted' => number_format((float)$user->wallet->balance, 2, ',', ' ') . ' MAD',
                    ],
                ],
            ],
        ], 201);
    }

    /**
     * Login user and create token.
     * Route: POST /api/login
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => __('L\'email est obligatoire.'),
            'email.email' => __('L\'email doit être une adresse email valide.'),
            'password.required' => __('Le mot de passe est obligatoire.'),
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
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

        // Load relationships for dashboard
        $user->load('wallet');

        return response()->json([
            'success' => true,
            'message' => __('Connexion réussie.'),
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'company' => $user->company,
                    'role' => $user->role ?? 'user',
                    'email_verified_at' => $user->email_verified_at?->toISOString(),
                    'wallet' => [
                        'id' => $user->wallet->id,
                        'balance' => number_format((float)$user->wallet->balance, 2, '.', ''),
                        'balance_formatted' => number_format((float)$user->wallet->balance, 2, ',', ' ') . ' MAD',
                    ],
                ],
            ],
        ], 200);
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

        // Check if this is an impersonation session (for frontend)
        // Frontend impersonation uses tokens, so we check if there's an impersonation token in session
        $isImpersonated = session()->has('impersonator_id') || session()->has('impersonation_token');
        $impersonator = null;
        
        if ($isImpersonated && session()->has('impersonator_id')) {
            $impersonatorId = session()->get('impersonator_id');
            $impersonator = \App\Models\User::find($impersonatorId);
        }

        $response = [
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'company' => $user->company,
                'role' => $user->role ?? 'user',
                'email_verified_at' => $user->email_verified_at?->toISOString(),
                'wallet' => [
                    'id' => $user->wallet->id,
                    'balance' => number_format((float)$user->wallet->balance, 2, '.', ''),
                    'balance_formatted' => number_format((float)$user->wallet->balance, 2, ',', ' ') . ' MAD',
                ],
            ],
        ];

        if ($isImpersonated && $impersonator) {
            $response['is_impersonated'] = true;
            $response['impersonator'] = [
                'id' => $impersonator->id,
                'name' => $impersonator->name,
                'email' => $impersonator->email,
            ];
        }

        return response()->json($response);
    }

    /**
     * Stop impersonation (for frontend API).
     *
     * Route: POST /api/impersonate/stop (auth:sanctum)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stopImpersonation(Request $request)
    {
        // Check both session and token-based impersonation
        $hasSessionImpersonation = session()->has('impersonator_id');
        $hasTokenImpersonation = session()->has('impersonation_token');
        
        if (!$hasSessionImpersonation && !$hasTokenImpersonation) {
            return response()->json([
                'success' => false,
                'message' => 'You are not currently impersonating anyone.',
            ], 400);
        }

        $impersonatedUser = $request->user();
        $impersonatorId = null;
        $impersonator = null;
        
        if ($hasSessionImpersonation) {
            $impersonatorId = session()->get('impersonator_id');
            $impersonator = \App\Models\User::findOrFail($impersonatorId);
        }

        // Revoke the impersonation token
        $request->user()->currentAccessToken()?->delete();

        // Log the stop impersonation action
        if ($impersonatorId) {
            try {
                \Illuminate\Support\Facades\DB::table('admin_action_logs')->insert([
                    'user_id' => $impersonatorId,
                    'route_name' => 'api.impersonate.stop',
                    'method' => 'POST',
                    'action_key' => 'stopped_impersonating',
                    'entity_key' => $impersonatedUser->role === 'organizer' ? 'organizer' : 'user',
                    'url' => $request->fullUrl(),
                    'subject_type' => \App\Models\User::class,
                    'subject_id' => $impersonatedUser->id,
                    'target_label' => $impersonatedUser->name . ' (' . $impersonatedUser->email . ')',
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'status_code' => 200,
                    'outcome' => 'success',
                    'details' => "Stopped impersonating user: {$impersonatedUser->name} ({$impersonatedUser->email})",
                    'payload' => json_encode([
                        'impersonated_user_id' => $impersonatedUser->id,
                        'impersonated_user_name' => $impersonatedUser->name,
                        'impersonated_user_email' => $impersonatedUser->email,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {
                // Don't fail if logging fails
            }
        }

        // Clear impersonation session data
        session()->forget(['impersonator_id', 'impersonator_name', 'impersonated_user_role', 'impersonation_token']);

        // Get backend admin URL from APP_URL config
        // APP_URL should be set to the backend base URL (e.g., https://seminairexpo.com/admin/public)
        $backendUrl = config('app.url');
        
        // If APP_URL is not configured, fall back to constructing from request
        if (!$backendUrl || $backendUrl === 'http://localhost') {
            $scheme = $request->getScheme();
            $host = $request->getHost();
            $port = $request->getPort();
            
            $backendUrl = $scheme . '://' . $host;
            if ($port && !in_array($port, [80, 443])) {
                $backendUrl .= ':' . $port;
            }
        }
        
        // Remove trailing slash
        $backendUrl = rtrim($backendUrl, '/');
        
        // Construct admin panel URL
        // APP_URL should already include the full backend path (e.g., /admin/public)
        // So we just append the admin route
        $redirectUrl = $backendUrl . '/admin/users';

        return response()->json([
            'success' => true,
            'message' => 'Impersonation stopped successfully.',
            'redirect_url' => $redirectUrl,
        ]);
    }

    /**
     * Update authenticated user's profile information.
     *
     * Route: PUT /api/user (auth:sanctum)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => __('Le nom est obligatoire.'),
            'email.required' => __('L\'email est obligatoire.'),
            'email.email' => __('L\'email doit être une adresse email valide.'),
            'email.unique' => __('Cet email est déjà utilisé.'),
            'phone.max' => __('Le numéro de téléphone ne doit pas dépasser 50 caractères.'),
            'company.max' => __('Le nom de l\'entreprise ne doit pas dépasser 255 caractères.'),
        ]);

        // If email changed, reset email verification
        if ($user->email !== $validated['email']) {
            $user->email_verified_at = null;
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'company' => $validated['company'] ?? null,
        ]);

        // Ensure wallet exists
        if (!$user->wallet) {
            $user->wallet()->create(['balance' => 0.00]);
            $user->refresh();
        }

        $user->load('wallet');

        return response()->json([
            'success' => true,
            'message' => __('Profil mis à jour avec succès.'),
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'company' => $user->company,
                    'role' => $user->role ?? 'user',
                    'email_verified_at' => $user->email_verified_at?->toISOString(),
                    'wallet' => [
                        'id' => $user->wallet->id,
                        'balance' => number_format((float)$user->wallet->balance, 2, '.', ''),
                        'balance_formatted' => number_format((float)$user->wallet->balance, 2, ',', ' ') . ' MAD',
                    ],
                ],
            ],
        ], 200);
    }

    /**
     * Update authenticated user's password (profile).
     *
     * Route: PUT /api/user/password (auth:sanctum)
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers(),
            ],
        ], [
            'current_password.required' => __('Le mot de passe actuel est obligatoire.'),
            'password.required' => __('Le nouveau mot de passe est obligatoire.'),
            'password.confirmed' => __('La confirmation du nouveau mot de passe ne correspond pas.'),
            'password.min' => __('Le nouveau mot de passe doit contenir au moins 8 caractères.'),
            'password.mixedCase' => __('Le nouveau mot de passe doit contenir des majuscules et des minuscules.'),
            'password.numbers' => __('Le nouveau mot de passe doit contenir au moins un chiffre.'),
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('Le mot de passe actuel est incorrect.')],
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Votre mot de passe a été mis à jour avec succès.'),
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
