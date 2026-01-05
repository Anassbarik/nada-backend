<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\HotelController;
use Illuminate\Support\Facades\Route;

// Public API routes
Route::get('/events', [EventController::class, 'index']);

// Event content pages (must come before /events/{slug} to avoid route conflicts)
Route::get('/events/{event:slug}/{type}', [EventController::class, 'getContentByType'])
    ->where('type', 'conditions|info|faq');

// Hotels by event slug (must come before /events/{slug} to avoid route conflicts)
Route::get('/events/{event:slug}/hotels', [HotelController::class, 'index']); // Uses slug via getRouteKeyName()
Route::get('/events/{event:slug}/hotels/{hotel:slug}', [HotelController::class, 'show']); // Hotel details within event context

// Event by slug (generic route - must come after specific routes)
Route::get('/events/{slug}', [EventController::class, 'show']);

Route::get('/hotels', [HotelController::class, 'listAll']); // List all hotels

// Partners
Route::get('/partners', [\App\Http\Controllers\Api\PartnerController::class, 'apiIndex']);

// Booking routes (public - no authentication required)
Route::post('/bookings', [BookingController::class, 'store']);
Route::post('/events/{event:slug}/hotels/{hotel:slug}/bookings', [BookingController::class, 'store']); // Legacy route

// Protected booking routes (for authenticated users to view their bookings)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus']);
});
