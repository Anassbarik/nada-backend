<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
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
}
