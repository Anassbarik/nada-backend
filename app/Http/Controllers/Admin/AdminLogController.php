<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActionLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminLogController extends Controller
{
    public function index(Request $request)
    {
        // Defense-in-depth (route is already protected by role:super-admin)
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403, 'You do not have permission to view admin logs.');
        }

        $query = AdminActionLog::query()->with('user')->latest();

        $defaultDays = (int) config('admin_logs.default_list_days', 30);
        $hasAnyFilter = $request->filled('action')
            || $request->filled('entity')
            || $request->filled('user_id')
            || $request->filled('q')
            || $request->filled('from')
            || $request->filled('to');
        $defaultDaysApplied = false;

        // If the super-admin opens the logs page with no filters, keep it bounded by default.
        if (!$hasAnyFilter && $defaultDays > 0) {
            $query->where('created_at', '>=', now()->subDays($defaultDays));
            $defaultDaysApplied = true;
        }

        if ($request->filled('action')) {
            $query->where('action_key', (string) $request->input('action'));
        }

        if ($request->filled('entity')) {
            $query->where('entity_key', (string) $request->input('entity'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->input('user_id'));
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            // Avoid expensive broad scans on 1-2 character queries.
            if (mb_strlen($q) < 3) {
                $q = '';
            }
        } else {
            $q = '';
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                // Sanitize search input to prevent SQL injection
                $sanitizedQ = \App\Services\InputSanitizer::sanitizeSearch($q);
                $sub->where('target_label', 'like', "%{$sanitizedQ}%")
                    ->orWhere('details', 'like', "%{$sanitizedQ}%")
                    ->orWhere('entity_key', 'like', "%{$sanitizedQ}%")
                    ->orWhere('action_key', 'like', "%{$sanitizedQ}%")
                    ->orWhereHas('user', function ($u) use ($sanitizedQ) {
                        $u->where('name', 'like', "%{$sanitizedQ}%")
                            ->orWhere('email', 'like', "%{$sanitizedQ}%");
                    });
            });
        }

        if ($request->filled('from')) {
            $from = Carbon::parse($request->input('from'))->startOfDay();
            $query->where('created_at', '>=', $from);
        }

        if ($request->filled('to')) {
            $to = Carbon::parse($request->input('to'))->endOfDay();
            $query->where('created_at', '<=', $to);
        }

        $logs = $query->paginate(25)->withQueryString();

        $admins = User::whereIn('role', ['admin', 'super-admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        $actionOptions = [
            'created',
            'updated',
            'deleted',
            'duplicated',
            'status_changed',
            'refunded',
            'toggled',
            'reordered',
            'sent',
        ];

        $entityOptions = [
            'event',
            'hotel',
            'package',
            'booking',
            'airport',
            'invoice',
            'partner',
            'admin',
            'system',
        ];

        return view('admin.logs.index', compact(
            'logs',
            'admins',
            'defaultDaysApplied',
            'defaultDays',
            'actionOptions',
            'entityOptions'
        ));
    }

    public function show(AdminActionLog $log)
    {
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403, 'You do not have permission to view admin logs.');
        }

        $log->load('user');

        return view('admin.logs.show', compact('log'));
    }
}


