<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of users (organizers and regular users).
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::whereIn('role', ['user', 'organizer'])
            ->withCount('bookings')
            ->latest();

        // Filter by role
        if ($request->has('role') && $request->role !== '') {
            $query->where('role', $request->role);
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $search = \App\Services\InputSanitizer::sanitizeSearch($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        // Ensure we're viewing a regular user or organizer
        if (!in_array($user->role, ['user', 'organizer'])) {
            abort(404);
        }

        $user->load(['bookings.accommodation', 'bookings.hotel', 'bookings.package', 'wallet']);

        return view('admin.users.show', compact('user'));
    }
}

