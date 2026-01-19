<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

// Sanctum CSRF cookie route (for SPA authentication)
Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

// Public API routes
Route::get('/maintenance', [\App\Http\Controllers\Api\MaintenanceController::class, 'index']);
Route::get('/events', [EventController::class, 'index']);

// Event content pages (must come before /events/{slug} to avoid route conflicts)
Route::get('/events/{slug}/{type}', [EventController::class, 'getContentByType'])
    ->where('type', 'conditions|info|faq');

// Hotels by event slug (must come before /events/{slug} to avoid route conflicts)
Route::get('/events/{slug}/hotels', [HotelController::class, 'index']);
Route::get('/events/{slug}/hotels/{hotel:slug}', [HotelController::class, 'show']); // Hotel details within event context

// Airports by event slug
Route::get('/events/{slug}/airports', [\App\Http\Controllers\Api\AirportController::class, 'index']); // List airports for an event

// Event by slug (generic route - must come after specific routes)
Route::get('/events/{slug}', [EventController::class, 'show']);

Route::get('/hotels', [HotelController::class, 'listAll']); // List all hotels

// Partners
Route::get('/partners', [\App\Http\Controllers\Api\PartnerController::class, 'apiIndex']);

// Newsletter (public)
Route::post('/newsletter/subscribe', [\App\Http\Controllers\Api\NewsletterController::class, 'subscribe'])->middleware('throttle:5,1');
Route::post('/newsletter/unsubscribe', [\App\Http\Controllers\Api\NewsletterController::class, 'unsubscribe'])->middleware('throttle:5,1');
Route::get('/newsletter/unsubscribe', [\App\Http\Controllers\Api\NewsletterController::class, 'unsubscribeGet'])->name('newsletter.unsubscribe.get');

// Authentication routes (public, with rate limiting)
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1'); // 5 attempts per minute
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1'); // 5 attempts per minute

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user', [AuthController::class, 'update']);
    Route::put('/user/password', [AuthController::class, 'updatePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Wallet routes
    Route::get('/wallet', [WalletController::class, 'index']);
    Route::get('/wallet/balance', [WalletController::class, 'balance']);

    // Voucher routes (only paid bookings)
    Route::get('/vouchers', [VoucherController::class, 'index']);
    Route::get('/vouchers/{voucher}', [VoucherController::class, 'show']);

    // Booking routes (now requires authentication)
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::post('/events/{slug}/hotels/{hotel:slug}/bookings', [BookingController::class, 'store']); // Legacy route
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus']);
});
