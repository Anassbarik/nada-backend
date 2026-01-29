<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('admins', 'view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasPermission('admins', 'view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('admins', 'create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Super-admins can only be edited by super-admins
        if ($model->isSuperAdmin() && !$user->isSuperAdmin()) {
            return false;
        }

        return $user->hasPermission('admins', 'edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Cannot delete yourself
        if ($user->id === $model->id) {
            return false;
        }

        // Super-admins can only be deleted by super-admins
        if ($model->isSuperAdmin() && !$user->isSuperAdmin()) {
            return false;
        }

        return $user->hasPermission('admins', 'delete');
    }

    /**
     * Determine whether the user can impersonate the model.
     * Note: This checks the actual authenticated user, not the impersonator.
     * If you're already impersonating, you cannot impersonate another user.
     */
    public function impersonate(User $user, User $model): bool
    {
        // Get the authenticated user directly (the $user parameter should be the same, but be explicit)
        $authenticatedUser = auth()->user();
        
        // Safety check - ensure we have an authenticated user
        if (!$authenticatedUser) {
            return false;
        }

        // Cannot impersonate yourself
        if ($authenticatedUser->id === $model->id) {
            return false;
        }

        // Only super-admins can impersonate (check actual role property directly)
        // Check the role property directly from the database, not through any method
        if ($authenticatedUser->role !== 'super-admin') {
            return false;
        }

        // If currently impersonating someone, cannot impersonate another user
        // (Must stop impersonation first)
        // Check if session has impersonator_id that's different from current user
        if (session()->has('impersonator_id')) {
            $impersonatorId = session()->get('impersonator_id');
            // If the impersonator_id in session is different from current user, we're impersonating
            if ($impersonatorId && (int)$impersonatorId !== (int)$authenticatedUser->id) {
                return false;
            }
        }

        // Cannot impersonate other super-admins
        if ($model->role === 'super-admin') {
            return false;
        }

        // Can impersonate admins, organizers, and regular users
        return in_array($model->role, ['admin', 'organizer', 'user']);
    }
}
