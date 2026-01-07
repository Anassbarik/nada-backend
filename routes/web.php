<?php

use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\HotelController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        $stats = [
            'revenue' => \App\Models\Booking::whereDate('created_at', today())
                ->where('status', 'confirmed')
                ->with('package')
                ->get()
                ->sum(fn($b) => $b->package->prix_ttc ?? 0),
            'bookings' => \App\Models\Booking::whereDate('created_at', today())->count(),
            'recent' => \App\Models\Booking::with(['event', 'hotel', 'package'])->latest()->take(5)->get(),
        ];
        return view('admin.dashboard', compact('stats'));
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
        'recent' => \App\Models\Booking::with(['event', 'hotel', 'package'])->latest()->take(5)->get(),
    ];
    return view('admin.dashboard', compact('stats'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Routes (require admin role)
    Route::middleware([\App\Http\Middleware\SetLocale::class, 'role:admin'])->name('admin.')->group(function () {
        // Events
        Route::resource('events', EventController::class);
        
        // Event Content
        Route::get('events/{event}/content', [\App\Http\Controllers\Admin\EventContentController::class, 'index'])->name('events.content.index');
        Route::get('events/{event}/content/{pageType}', [\App\Http\Controllers\Admin\EventContentController::class, 'edit'])->name('events.content.edit');
        
        // Hotels
        Route::get('events/{event}/hotels', [HotelController::class, 'index'])->name('events.hotels.index');
        Route::get('events/{event}/hotels/create', [HotelController::class, 'create'])->name('events.hotels.create');
        Route::post('events/{event}/hotels', [HotelController::class, 'store'])->name('events.hotels.store');
        Route::get('hotels/{hotel}/edit', [HotelController::class, 'edit'])->name('hotels.edit');
        Route::put('hotels/{hotel}', [HotelController::class, 'update'])->name('hotels.update');
        Route::delete('hotels/{hotel}', [HotelController::class, 'destroy'])->name('hotels.destroy');
        
        // Hotel Images
        Route::get('hotels/{hotel}/images', [\App\Http\Controllers\Admin\HotelImageController::class, 'index'])->name('hotels.images.index');
        Route::post('hotels/{hotel}/images', [\App\Http\Controllers\Admin\HotelImageController::class, 'store'])->name('hotels.images.store');
        Route::patch('hotels/{hotel}/images/{image}', [\App\Http\Controllers\Admin\HotelImageController::class, 'update'])->name('hotels.images.update');
        Route::delete('hotels/{hotel}/images/{image}', [\App\Http\Controllers\Admin\HotelImageController::class, 'destroy'])->name('hotels.images.destroy');
        Route::patch('hotels/{hotel}/images/reorder', [\App\Http\Controllers\Admin\HotelImageController::class, 'reorder'])->name('hotels.images.reorder');
        
        // Packages
        Route::get('hotels/{hotel}/packages', [\App\Http\Controllers\Admin\PackageController::class, 'index'])->name('hotels.packages.index');
        Route::post('hotels/{hotel}/packages', [\App\Http\Controllers\Admin\PackageController::class, 'store'])->name('hotels.packages.store');
        Route::get('hotels/{hotel}/packages/{package}/edit', [\App\Http\Controllers\Admin\PackageController::class, 'edit'])->name('hotels.packages.edit');
        Route::put('hotels/{hotel}/packages/{package}', [\App\Http\Controllers\Admin\PackageController::class, 'update'])->name('hotels.packages.update');
        Route::delete('hotels/{hotel}/packages/{package}', [\App\Http\Controllers\Admin\PackageController::class, 'destroy'])->name('hotels.packages.destroy');
        
        // Bookings
        Route::get('bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
        Route::patch('bookings/{booking}/status', [AdminBookingController::class, 'updateStatus'])->name('bookings.updateStatus');
        Route::post('bookings/{booking}/refund', [AdminBookingController::class, 'refund'])->name('bookings.refund');
        Route::delete('bookings/{booking}', [AdminBookingController::class, 'destroy'])->name('bookings.destroy');

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
    });
});

require __DIR__.'/auth.php';
