<?php

use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\HotelController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\AdminLogController;
use App\Http\Controllers\Admin\FlightController as AdminFlightController;
use App\Http\Controllers\ProfileController;
use App\Models\Accommodation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

// Cache clearing route (secure, requires token from .env)
Route::get('/clear-cache', [\App\Http\Controllers\CacheController::class, 'clear']);

// Route model binding for accommodations (using 'event' parameter name for backward compatibility)
Route::bind('event', function ($value) {
    return Accommodation::where('slug', $value)->firstOrFail();
});

// Route model binding for admin parameter (for impersonation routes)
Route::bind('admin', function ($value) {
    return \App\Models\User::findOrFail($value);
});

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->isOrganizer()) {
            return redirect()->route('organizer.dashboard');
        }
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            $stats = [
                'revenue' => \App\Models\Booking::whereDate('created_at', today())
                    ->where('status', 'confirmed')
                    ->with('package')
                    ->get()
                    ->sum(fn($b) => $b->package->prix_ttc ?? 0),
                'bookings' => \App\Models\Booking::whereDate('created_at', today())->count(),
                'recent' => \App\Models\Booking::with(['accommodation', 'hotel', 'package'])->latest()->take(5)->get(),
            ];
            return view('admin.dashboard', compact('stats'));
        }
    }
    return view('auth.login');
});

Route::get('/dashboard', function () {
    $stats = [
        'revenue' => \App\Models\Booking::whereDate('created_at', today())
            ->where('status', 'confirmed')
            ->with('package')
            ->get()
            ->sum(fn($b) => $b->package->prix_ttc ?? 0),
        'bookings' => \App\Models\Booking::whereDate('created_at', today())->count(),
        'recent' => \App\Models\Booking::with(['accommodation', 'hotel', 'package'])->latest()->take(5)->get(),
    ];
    return view('admin.dashboard', compact('stats'));
})->middleware(['auth', 'verified', 'role:admin'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Organizer routes
    Route::middleware('role:organizer')->prefix('organizer')->name('organizer.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\OrganizerController::class, 'dashboard'])->name('dashboard');
        Route::get('/bookings', [\App\Http\Controllers\OrganizerController::class, 'bookings'])->name('bookings');
        Route::get('/bookings/{booking}/voucher', [\App\Http\Controllers\OrganizerController::class, 'downloadVoucher'])->name('bookings.voucher');
        Route::get('/flights', [\App\Http\Controllers\OrganizerController::class, 'flights'])->name('flights');
        Route::get('/commissions', [\App\Http\Controllers\OrganizerController::class, 'commissions'])->name('commissions');
    });

    // Stop impersonation route (accessible to any authenticated user when impersonating)
    Route::middleware('auth')->post('admin/impersonate/stop', [\App\Http\Controllers\Admin\AdminController::class, 'stopImpersonating'])->name('admin.impersonate.stop');

    // Admin Routes (require admin role)
    Route::middleware([\App\Http\Middleware\SetLocale::class, 'role:admin', \App\Http\Middleware\LogAdminActions::class])->name('admin.')->group(function () {
        // Accommodation Events (renamed from Events)
        Route::resource('events', EventController::class)->parameters(['events' => 'event:slug']);
        Route::post('events/{event}/duplicate', [EventController::class, 'duplicate'])->name('events.duplicate');

        // Event Packages
        Route::get('event-packages', [\App\Http\Controllers\Admin\EventPackageController::class, 'index'])->name('event-packages.index');

        // Event Content
        Route::get('events/{event}/content', [\App\Http\Controllers\Admin\EventContentController::class, 'index'])->name('events.content.index');
        Route::get('events/{event}/content/{pageType}', [\App\Http\Controllers\Admin\EventContentController::class, 'edit'])->name('events.content.edit');
        Route::put('events/{event}/content/{pageType}', [\App\Http\Controllers\Admin\EventContentController::class, 'update'])->name('events.content.update');

        // Airports
        Route::get('events/{event}/airports', [\App\Http\Controllers\Admin\AirportController::class, 'index'])->name('events.airports.index');
        Route::get('events/{event}/airports/create', [\App\Http\Controllers\Admin\AirportController::class, 'create'])->name('events.airports.create');
        Route::post('events/{event}/airports', [\App\Http\Controllers\Admin\AirportController::class, 'store'])->name('events.airports.store');
        Route::get('events/{event}/airports/{airport}/edit', [\App\Http\Controllers\Admin\AirportController::class, 'edit'])->name('events.airports.edit');
        Route::put('events/{event}/airports/{airport}', [\App\Http\Controllers\Admin\AirportController::class, 'update'])->name('events.airports.update');
        Route::delete('events/{event}/airports/{airport}', [\App\Http\Controllers\Admin\AirportController::class, 'destroy'])->name('events.airports.destroy');
        Route::post('events/{event}/airports/{airport}/duplicate', [\App\Http\Controllers\Admin\AirportController::class, 'duplicate'])->name('events.airports.duplicate');

        // Hotels
        Route::get('events/{event}/hotels', [HotelController::class, 'index'])->name('events.hotels.index');
        Route::get('events/{event}/hotels/create', [HotelController::class, 'create'])->name('events.hotels.create');
        Route::post('events/{event}/hotels', [HotelController::class, 'store'])->name('events.hotels.store');
        Route::get('hotels/{hotel}/edit', [HotelController::class, 'edit'])->name('hotels.edit');
        Route::put('hotels/{hotel}', [HotelController::class, 'update'])->name('hotels.update');
        Route::delete('hotels/{hotel}', [HotelController::class, 'destroy'])->name('hotels.destroy');
        Route::post('hotels/{hotel}/duplicate', [HotelController::class, 'duplicate'])->name('hotels.duplicate');

        // Hotel Images
        Route::get('hotels/{hotel}/images', [\App\Http\Controllers\Admin\HotelImageController::class, 'index'])->name('hotels.images.index');
        Route::post('hotels/{hotel}/images', [\App\Http\Controllers\Admin\HotelImageController::class, 'store'])->name('hotels.images.store');
        Route::patch('hotels/{hotel}/images/{image}', [\App\Http\Controllers\Admin\HotelImageController::class, 'update'])->name('hotels.images.update');
        Route::delete('hotels/{hotel}/images/{image}', [\App\Http\Controllers\Admin\HotelImageController::class, 'destroy'])->name('hotels.images.destroy');
        Route::patch('hotels/{hotel}/images/reorder', [\App\Http\Controllers\Admin\HotelImageController::class, 'reorder'])->name('hotels.images.reorder');

        // Packages
        Route::get('hotels/{hotel}/packages', [\App\Http\Controllers\Admin\PackageController::class, 'index'])->name('hotels.packages.index');
        Route::get('hotels/{hotel}/packages/create', [\App\Http\Controllers\Admin\PackageController::class, 'create'])->name('hotels.packages.create');
        Route::post('hotels/{hotel}/packages', [\App\Http\Controllers\Admin\PackageController::class, 'store'])->name('hotels.packages.store');
        Route::get('hotels/{hotel}/packages/{package}/edit', [\App\Http\Controllers\Admin\PackageController::class, 'edit'])->name('hotels.packages.edit');
        Route::put('hotels/{hotel}/packages/{package}', [\App\Http\Controllers\Admin\PackageController::class, 'update'])->name('hotels.packages.update');
        Route::delete('hotels/{hotel}/packages/{package}', [\App\Http\Controllers\Admin\PackageController::class, 'destroy'])->name('hotels.packages.destroy');
        Route::post('hotels/{hotel}/packages/{package}/duplicate', [\App\Http\Controllers\Admin\PackageController::class, 'duplicate'])->name('hotels.packages.duplicate');

        // Bookings
        Route::get('bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
        Route::patch('bookings/{booking}/status', [AdminBookingController::class, 'updateStatus'])->name('bookings.updateStatus');
        Route::post('bookings/{booking}/refund', [AdminBookingController::class, 'refund'])->name('bookings.refund');
        Route::delete('bookings/{booking}', [AdminBookingController::class, 'destroy'])->name('bookings.destroy');
        Route::get('bookings/{booking}/payment-document', [AdminBookingController::class, 'downloadPaymentDocument'])->name('bookings.downloadPaymentDocument');
        Route::get('bookings/{booking}/flight-ticket', [AdminBookingController::class, 'downloadFlightTicket'])->name('bookings.downloadFlightTicket');

        // Flights (per event)
        Route::get('events/{accommodation}/flights', [AdminFlightController::class, 'index'])->name('flights.index');
        Route::get('events/{accommodation}/flights/create', [AdminFlightController::class, 'create'])->name('flights.create');
        Route::post('events/{accommodation}/flights', [AdminFlightController::class, 'store'])->name('flights.store');
        Route::get('events/{accommodation}/flights/{flight}', [AdminFlightController::class, 'show'])->name('flights.show');
        Route::get('events/{accommodation}/flights/{flight}/edit', [AdminFlightController::class, 'edit'])->name('flights.edit');
        Route::patch('events/{accommodation}/flights/{flight}', [AdminFlightController::class, 'update'])->name('flights.update');
        Route::post('events/{accommodation}/flights/{flight}/duplicate', [AdminFlightController::class, 'duplicate'])->name('flights.duplicate');
        Route::delete('events/{accommodation}/flights/{flight}', [AdminFlightController::class, 'destroy'])->name('flights.destroy');
        Route::get('events/{accommodation}/flights/{flight}/credentials', [AdminFlightController::class, 'downloadCredentials'])->name('flights.downloadCredentials');
        Route::get('events/{accommodation}/flights/export', [AdminFlightController::class, 'exportForAccommodation'])->name('flights.exportAccommodation');
        Route::get('events/{accommodation}/flights/{flight}/export', [AdminFlightController::class, 'exportSingle'])->name('flights.exportSingle');

        // Transfers (per event)
        Route::get('events/{accommodation}/transfers', [App\Http\Controllers\Admin\TransferController::class, 'index'])->name('transfers.index');
        Route::get('events/{accommodation}/transfers/create', [App\Http\Controllers\Admin\TransferController::class, 'create'])->name('transfers.create');
        Route::post('events/{accommodation}/transfers', [App\Http\Controllers\Admin\TransferController::class, 'store'])->name('transfers.store');
        Route::get('events/{accommodation}/transfers/{transfer}/edit', [App\Http\Controllers\Admin\TransferController::class, 'edit'])->name('transfers.edit');
        Route::patch('events/{accommodation}/transfers/{transfer}', [App\Http\Controllers\Admin\TransferController::class, 'update'])->name('transfers.update');
        Route::post('events/{accommodation}/transfers/{transfer}/duplicate', [App\Http\Controllers\Admin\TransferController::class, 'duplicate'])->name('transfers.duplicate');
        Route::delete('events/{accommodation}/transfers/{transfer}', [App\Http\Controllers\Admin\TransferController::class, 'destroy'])->name('transfers.destroy');
        Route::get('events/{accommodation}/transfers/export', [App\Http\Controllers\Admin\TransferController::class, 'exportForAccommodation'])->name('transfers.exportAccommodation');
        Route::get('events/{accommodation}/transfers/{transfer}/export', [App\Http\Controllers\Admin\TransferController::class, 'exportSingle'])->name('transfers.exportSingle');

        // Global Transfers (Sidebar)
        Route::get('transfers', [App\Http\Controllers\Admin\TransferController::class, 'globalIndex'])->name('transfers.global-index');
        Route::get('transfers/create', [App\Http\Controllers\Admin\TransferController::class, 'createStandalone'])->name('transfers.create-standalone');
        Route::post('transfers', [App\Http\Controllers\Admin\TransferController::class, 'storeStandalone'])->name('transfers.store-standalone');
        Route::post('transfers/{transfer}/duplicate', [App\Http\Controllers\Admin\TransferController::class, 'duplicateStandalone'])->name('transfers.duplicate-standalone');
        Route::get('transfers/export/all', [App\Http\Controllers\Admin\TransferController::class, 'exportAll'])->name('transfers.exportAll');

        // Vehicle Types
        Route::resource('vehicle-types', \App\Http\Controllers\Admin\VehicleTypeController::class);

        // Vehicle Classes
        Route::resource('vehicle-classes', \App\Http\Controllers\Admin\VehicleClassController::class);

        // Standalone Flights management (separate from accommodation flights)
        Route::get('flights', [AdminFlightController::class, 'globalIndex'])->name('standalone.flights.index');
        Route::get('flights/create', [AdminFlightController::class, 'createStandalone'])->name('standalone.flights.create');
        Route::post('flights', [AdminFlightController::class, 'storeStandalone'])->name('standalone.flights.store');
        Route::get('flights/{flight}', [AdminFlightController::class, 'showStandalone'])->name('standalone.flights.show');
        Route::get('flights/{flight}/edit', [AdminFlightController::class, 'editStandalone'])->name('standalone.flights.edit');
        Route::patch('flights/{flight}', [AdminFlightController::class, 'updateStandalone'])->name('standalone.flights.update');
        Route::post('flights/{flight}/duplicate', [AdminFlightController::class, 'duplicateStandalone'])->name('standalone.flights.duplicate');
        Route::delete('flights/{flight}', [AdminFlightController::class, 'destroyStandalone'])->name('standalone.flights.destroy');
        Route::get('flights/{flight}/credentials', [AdminFlightController::class, 'downloadCredentialsStandalone'])->name('standalone.flights.downloadCredentials');
        Route::get('flights/export/all', [AdminFlightController::class, 'exportAll'])->name('standalone.flights.exportAll');

        // Invoices
        Route::prefix('admin')->group(function () {
            Route::resource('invoices', InvoiceController::class);
            Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'stream'])->name('invoices.pdf');
            Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
        });

        // Partners
        Route::resource('partners', \App\Http\Controllers\Admin\PartnerController::class);
        Route::patch('partners/{partner}/toggle-active', [\App\Http\Controllers\Admin\PartnerController::class, 'toggleActive'])->name('partners.toggle-active');
        Route::patch('partners/sort-order', [\App\Http\Controllers\Admin\PartnerController::class, 'updateSortOrder'])->name('partners.sort-order');
        Route::post('partners/{partner}/duplicate', [\App\Http\Controllers\Admin\PartnerController::class, 'duplicate'])->name('partners.duplicate');

        // Users (organizers and regular users) - only super-admin can manage
        Route::middleware('role:super-admin')->group(function () {
            Route::get('users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
            Route::get('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
            Route::post('users/{user}/impersonate', [\App\Http\Controllers\Admin\AdminController::class, 'impersonate'])->name('users.impersonate');
        });

        // Admins (only super-admin can manage)
        Route::middleware('role:super-admin')->group(function () {
            Route::resource('admins', \App\Http\Controllers\Admin\AdminController::class);
            Route::post('admins/{admin}/impersonate', [\App\Http\Controllers\Admin\AdminController::class, 'impersonate'])->name('admins.impersonate');
            Route::get('organizers/{organizer}/credentials', [EventController::class, 'downloadOrganizerCredentials'])->name('organizers.download-credentials');
            Route::get('logs', [AdminLogController::class, 'index'])->name('logs.index');
            Route::get('logs/{log}', [AdminLogController::class, 'show'])->name('logs.show');

            // Newsletter (only super-admin can manage)
            Route::get('newsletter', [\App\Http\Controllers\Admin\NewsletterController::class, 'index'])->name('newsletter.index');
            Route::get('newsletter/create', [\App\Http\Controllers\Admin\NewsletterController::class, 'create'])->name('newsletter.create');
            Route::post('newsletter/send', [\App\Http\Controllers\Admin\NewsletterController::class, 'send'])->name('newsletter.send');
            Route::delete('newsletter/{subscriber}', [\App\Http\Controllers\Admin\NewsletterController::class, 'destroy'])->name('newsletter.destroy');
            Route::post('newsletter/{subscriber}/unsubscribe', [\App\Http\Controllers\Admin\NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');
            Route::post('newsletter/{subscriber}/resubscribe', [\App\Http\Controllers\Admin\NewsletterController::class, 'resubscribe'])->name('newsletter.resubscribe');
        });

        // Maintenance
        Route::post('maintenance/toggle', [\App\Http\Controllers\Admin\MaintenanceController::class, 'toggle'])->name('maintenance.toggle');
    });
});

require __DIR__ . '/auth.php';
