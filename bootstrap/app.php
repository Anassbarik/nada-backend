<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Add CSP middleware for XSS protection (only active when CSP_ENABLED=true)
        $middleware->web(append: [
            \App\Http\Middleware\ContentSecurityPolicy::class,
            \App\Http\Middleware\SanitizeInput::class,
            \App\Http\Middleware\CheckActive::class,
        ]);

        // Add input sanitization to API routes
        $middleware->api(append: [
            \App\Http\Middleware\SanitizeInput::class,
            \App\Http\Middleware\CheckActive::class,
        ]);

        // Register role and permission middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function ($schedule): void {
        // Cancel pending bookings that are older than 48 hours
        // Run every hour to check for bookings that need to be cancelled
        $schedule->command('bookings:cancel-pending')->hourly();

        // Keep admin audit logs bounded so queries stay fast and storage doesn't grow unbounded.
        $schedule->command('admin:prune-logs')->daily()->withoutOverlapping();
    })
    ->create();
