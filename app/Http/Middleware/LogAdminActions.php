<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LogAdminActions
{
    /**
     * Log admin actions (typically state-changing requests) for audit purposes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        if (!config('admin_logs.enabled', true)) {
            return $response;
        }

        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return $response;
        }

        $route = $request->route();
        $routeName = $route?->getName();

        // Only log admin panel routes (those are named admin.* in this codebase)
        if (!$routeName || !Str::startsWith($routeName, 'admin.')) {
            return $response;
        }

        // Avoid logging the logs viewer itself
        if (Str::startsWith($routeName, 'admin.logs.')) {
            return $response;
        }

        $method = strtoupper($request->method());
        // Log "actions" (state-changing requests) to avoid huge volumes from page views
        $allowed = config('admin_logs.methods', ['POST', 'PUT', 'PATCH', 'DELETE']);
        if (!in_array($method, $allowed, true)) {
            return $response;
        }

        $subjectModel = $this->inferSubjectModelFromRoute($route?->parameters() ?? []);
        $subjectType = $subjectModel ? get_class($subjectModel) : null;
        $subjectId = $subjectModel ? $subjectModel->getKey() : null;

        $actionKey = $this->inferActionKey($routeName);
        $entityKey = $this->inferEntityKey($subjectModel, $routeName);
        $targetLabel = $this->inferTargetLabel($subjectModel, $entityKey);

        // Sanitize payload to avoid storing secrets
        $safeInput = $request->except([
            '_token',
            'password',
            'password_confirmation',
            'current_password',
            'new_password',
        ]);

        $payload = [
            'input' => $safeInput,
            'query' => $request->query(),
            'files' => array_keys($request->allFiles()),
            'route_params' => $this->safeRouteParams($route?->parameters() ?? []),
        ];

        $payload = $this->truncatePayloadIfNeeded($payload);

        $url = $this->truncateString($request->fullUrl(), 2000);
        $userAgent = $this->truncateString((string) $request->userAgent(), 1000);
        $statusCode = method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null;
        $outcome = ($statusCode !== null && $statusCode >= 400) ? 'failed' : 'success';
        $details = $this->inferDetails($routeName, $safeInput);

        // Never let logging break admin actions.
        try {
            DB::table('admin_action_logs')->insert([
                'user_id' => $user->id,
                'route_name' => $routeName,
                'method' => $method,
                'action_key' => $actionKey,
                'entity_key' => $entityKey,
                'url' => $url,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'target_label' => $targetLabel,
                'ip' => $request->ip(),
                'user_agent' => $userAgent,
                'status_code' => $statusCode,
                'outcome' => $outcome,
                'details' => $details,
                'payload' => $payload ? json_encode($payload) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Admin action log insert failed', [
                'route' => $routeName,
                'method' => $method,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $response;
    }

    /**
     * @param array<string, mixed> $routeParams
     */
    private function inferSubjectModelFromRoute(array $routeParams): ?Model
    {
        foreach ($routeParams as $value) {
            if ($value instanceof Model) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Convert route params into a safe, JSON-serializable structure.
     *
     * @param array<string, mixed> $routeParams
     * @return array<string, mixed>
     */
    private function safeRouteParams(array $routeParams): array
    {
        $out = [];
        foreach ($routeParams as $key => $value) {
            if ($value instanceof Model) {
                $out[$key] = [
                    'type' => get_class($value),
                    'id' => $value->getKey(),
                ];
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $out[$key] = $value;
                continue;
            }

            $out[$key] = '[non-scalar]';
        }

        return $out;
    }

    private function inferActionKey(string $routeName): string
    {
        if (Str::endsWith($routeName, '.store')) {
            return 'created';
        }
        if (Str::endsWith($routeName, '.update')) {
            return 'updated';
        }
        if (Str::endsWith($routeName, '.destroy')) {
            return 'deleted';
        }
        if (Str::endsWith($routeName, '.duplicate')) {
            return 'duplicated';
        }
        if (Str::contains($routeName, 'updateStatus')) {
            return 'status_changed';
        }
        if (Str::contains($routeName, 'refund')) {
            return 'refunded';
        }
        if (Str::contains($routeName, 'toggle')) {
            return 'toggled';
        }
        if (Str::contains($routeName, 'reorder')) {
            return 'reordered';
        }
        if (Str::contains($routeName, 'send')) {
            return 'sent';
        }

        return 'updated';
    }

    private function inferEntityKey(?Model $subjectModel, string $routeName): string
    {
        if ($subjectModel) {
            $base = class_basename($subjectModel);
            return match ($base) {
                'Event' => 'event',
                'Hotel' => 'hotel',
                'Package' => 'package',
                'Booking' => 'booking',
                'Airport' => 'airport',
                'Invoice' => 'invoice',
                'Partner' => 'partner',
                'HotelImage' => 'hotel_image',
                'HotelNightPrice' => 'night_price',
                'User' => 'admin',
                default => Str::snake($base),
            };
        }

        if (Str::contains($routeName, 'maintenance')) {
            return 'system';
        }

        return 'system';
    }

    private function inferTargetLabel(?Model $subjectModel, string $entityKey): ?string
    {
        if (!$subjectModel) {
            return $entityKey === 'system' ? 'System' : null;
        }

        // Prefer the most "human" identifier available
        foreach (['name', 'title', 'nom_package', 'booking_reference', 'email', 'slug'] as $attr) {
            if ($subjectModel->getAttribute($attr)) {
                return (string) $subjectModel->getAttribute($attr);
            }
        }

        return '#' . $subjectModel->getKey();
    }

    /**
     * @param array<string, mixed> $safeInput
     */
    private function inferDetails(string $routeName, array $safeInput): ?string
    {
        if (Str::contains($routeName, 'bookings.updateStatus') && isset($safeInput['status'])) {
            return 'Status: ' . (string) $safeInput['status'];
        }

        if (Str::contains($routeName, 'bookings.refund')) {
            $amount = $safeInput['refund_amount'] ?? null;
            return $amount !== null ? ('Refund amount: ' . (string) $amount) : 'Refund processed';
        }

        if (Str::contains($routeName, 'partners.toggle-active') && array_key_exists('active', $safeInput)) {
            return 'Active: ' . ((bool) $safeInput['active'] ? 'Yes' : 'No');
        }

        if (Str::contains($routeName, 'maintenance.toggle')) {
            return 'Maintenance setting changed';
        }

        return null;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function truncatePayloadIfNeeded(array $payload): array
    {
        $maxBytes = (int) config('admin_logs.max_payload_bytes', 16384);
        $json = json_encode($payload);

        if (!is_string($json) || strlen($json) <= $maxBytes) {
            return $payload;
        }

        // Store a compact summary if payload is too large.
        return [
            '_truncated' => true,
            '_original_bytes' => is_string($json) ? strlen($json) : null,
            'input_keys' => isset($payload['input']) && is_array($payload['input']) ? array_slice(array_keys($payload['input']), 0, 200) : [],
            'query' => $payload['query'] ?? [],
            'files' => $payload['files'] ?? [],
            'route_params' => $payload['route_params'] ?? [],
        ];
    }

    private function truncateString(?string $value, int $max): ?string
    {
        if ($value === null) {
            return null;
        }
        if ($max <= 0) {
            return '';
        }
        if (mb_strlen($value) <= $max) {
            return $value;
        }
        return mb_substr($value, 0, $max);
    }
}


