<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Display a listing of admins.
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $admins = User::whereIn('role', ['admin', 'super-admin'])
            ->with('permissions')
            ->latest()
            ->paginate(15);

        return view('admin.admins.index', compact('admins'));
    }

    /**
     * Show the form for creating a new admin.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        $permissions = Permission::orderBy('resource')->orderBy('action')->get();
        $permissionsByResource = $permissions->groupBy('resource');

        return view('admin.admins.create', compact('permissionsByResource'));
    }

    /**
     * Store a newly created admin.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(['admin', 'super-admin'])],
            'permissions' => 'required_if:role,admin|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        // Only assign permissions if admin (super-admin has all permissions)
        if ($validated['role'] === 'admin' && isset($validated['permissions'])) {
            $user->permissions()->sync($validated['permissions']);
        }

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin created successfully.');
    }

    /**
     * Display the specified admin.
     */
    public function show(User $admin)
    {
        $this->authorize('view', $admin);

        $admin->load('permissions');
        return view('admin.admins.show', compact('admin'));
    }

    /**
     * Show the form for editing the specified admin.
     */
    public function edit(User $admin)
    {
        $this->authorize('update', $admin);

        // Prevent editing super-admin if current user is not super-admin
        if ($admin->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403, 'You cannot edit super-admin users.');
        }

        $permissions = Permission::orderBy('resource')->orderBy('action')->get();
        $permissionsByResource = $permissions->groupBy('resource');
        $admin->load('permissions');

        return view('admin.admins.edit', compact('admin', 'permissionsByResource'));
    }

    /**
     * Update the specified admin.
     */
    public function update(Request $request, User $admin)
    {
        $this->authorize('update', $admin);

        // Prevent editing super-admin if current user is not super-admin
        if ($admin->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403, 'You cannot edit super-admin users.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($admin->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::in(['admin', 'super-admin'])],
            'permissions' => 'required_if:role,admin|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $admin->name = $validated['name'];
        $admin->email = $validated['email'];

        if (!empty($validated['password'])) {
            $admin->password = Hash::make($validated['password']);
        }

        $admin->role = $validated['role'];
        $admin->save();

        // Only assign permissions if admin (super-admin has all permissions)
        if ($validated['role'] === 'admin' && isset($validated['permissions'])) {
            $admin->permissions()->sync($validated['permissions']);
        } else {
            // Clear permissions if changed to super-admin
            $admin->permissions()->detach();
        }

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin updated successfully.');
    }

    /**
     * Remove the specified admin.
     */
    public function destroy(User $admin)
    {
        $this->authorize('delete', $admin);

        // Prevent deleting super-admin if current user is not super-admin
        if ($admin->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403, 'You cannot delete super-admin users.');
        }

        // Prevent deleting yourself
        if ($admin->id === auth()->id()) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin deleted successfully.');
    }

    /**
     * Impersonate a user (admin, organizer, or regular user).
     */
    public function impersonate(User $user)
    {
        $this->authorize('impersonate', $user);

        $impersonator = auth()->user();

        // Store the original admin ID in session
        session()->put('impersonator_id', $impersonator->id);
        session()->put('impersonator_name', $impersonator->name);
        session()->put('impersonated_user_role', $user->role);

        // Log the impersonation action (before switching users)
        try {
            DB::table('admin_action_logs')->insert([
                'user_id' => $impersonator->id,
                'route_name' => request()->route()->getName(),
                'method' => 'POST',
                'action_key' => 'impersonated',
                'entity_key' => $user->role === 'organizer' ? 'organizer' : ($user->role === 'user' ? 'user' : 'admin'),
                'url' => request()->fullUrl(),
                'subject_type' => User::class,
                'subject_id' => $user->id,
                'target_label' => $user->name . ' (' . $user->email . ')',
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'status_code' => 302,
                'outcome' => 'success',
                'details' => "Impersonating {$user->role}: {$user->name} ({$user->email})",
                'payload' => json_encode([
                    'impersonated_user_id' => $user->id,
                    'impersonated_user_name' => $user->name,
                    'impersonated_user_email' => $user->email,
                    'impersonated_user_role' => $user->role,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Don't fail if logging fails
        }

        // Handle different user types
        if ($user->role === 'user') {
            // For regular users, generate a Sanctum token for frontend access
            $token = $user->createToken('impersonation-token', ['*'], now()->addHours(24))->plainTextToken;
            session()->put('impersonation_token', $token);
            
            // Log in as the user (for backend session)
            auth()->login($user);
            
            // Redirect to frontend with token
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
            $redirectUrl = $frontendUrl . '/dashboard?impersonation_token=' . $token;
            
            return redirect($redirectUrl);
        } elseif ($user->role === 'organizer') {
            // For organizers, log in and redirect to organizer dashboard
            auth()->login($user);
            return redirect()->route('organizer.dashboard')
                ->with('success', "You are now impersonating organizer {$user->name}.");
        } else {
            // For admins, log in and redirect to admin dashboard
            auth()->login($user);
            return redirect()->route('dashboard')
                ->with('success', "You are now impersonating {$user->name}.");
        }
    }

    /**
     * Stop impersonating and return to original admin account.
     */
    public function stopImpersonating()
    {
        if (!session()->has('impersonator_id')) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not currently impersonating anyone.');
        }

        $impersonatedUser = auth()->user();
        $impersonatorId = session()->get('impersonator_id');
        $impersonatedUserRole = session()->get('impersonated_user_role', 'user');
        $impersonator = User::findOrFail($impersonatorId);

        // Log the stop impersonation action (before switching users)
        try {
            DB::table('admin_action_logs')->insert([
                'user_id' => $impersonatorId,
                'route_name' => 'admin.impersonate.stop',
                'method' => 'POST',
                'action_key' => 'stopped_impersonating',
                'entity_key' => $impersonatedUserRole === 'organizer' ? 'organizer' : ($impersonatedUserRole === 'user' ? 'user' : 'admin'),
                'url' => request()->fullUrl(),
                'subject_type' => User::class,
                'subject_id' => $impersonatedUser->id,
                'target_label' => $impersonatedUser->name . ' (' . $impersonatedUser->email . ')',
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'status_code' => 302,
                'outcome' => 'success',
                'details' => "Stopped impersonating {$impersonatedUserRole}: {$impersonatedUser->name} ({$impersonatedUser->email})",
                'payload' => json_encode([
                    'impersonated_user_id' => $impersonatedUser->id,
                    'impersonated_user_name' => $impersonatedUser->name,
                    'impersonated_user_email' => $impersonatedUser->email,
                    'impersonated_user_role' => $impersonatedUserRole,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Don't fail if logging fails
        }

        // Log back in as the original admin
        auth()->login($impersonator);

        // Regenerate session to ensure authentication state is properly updated
        request()->session()->regenerate();

        // Clear impersonation session data
        session()->forget(['impersonator_id', 'impersonator_name', 'impersonated_user_role']);

        // Save session to ensure it's persisted before redirect
        session()->save();

        // Redirect based on where we were impersonating from
        if ($impersonatedUserRole === 'user' || $impersonatedUserRole === 'organizer') {
            return redirect()->route('admin.users.index')
                ->with('success', 'You have stopped impersonating and returned to your account.');
        }

        return redirect()->route('admin.admins.index')
            ->with('success', 'You have stopped impersonating and returned to your account.');
    }
}
