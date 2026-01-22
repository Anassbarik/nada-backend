<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\Booking;
use App\Models\Voucher;
use App\Services\DualStorageService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->isOrganizer()) {
                abort(403, 'Access denied. Organizer access only.');
            }
            return $next($request);
        });
    }

    /**
     * Display the organizer dashboard.
     */
    public function dashboard()
    {
        $organizer = Auth::user();
        $event = $organizer->organizedAccommodations()->first();

        if (!$event) {
            return view('organizer.no-event');
        }

        // Get event statistics
        $stats = [
            'total_bookings' => Booking::where('accommodation_id', $event->id)->count(),
            'confirmed_bookings' => Booking::where('accommodation_id', $event->id)
                ->where('status', 'confirmed')
                ->count(),
            'pending_bookings' => Booking::where('accommodation_id', $event->id)
                ->where('status', 'pending')
                ->count(),
            'cancelled_bookings' => Booking::where('accommodation_id', $event->id)
                ->whereIn('status', ['cancelled', 'refunded'])
                ->count(),
            'total_revenue' => Booking::where('accommodation_id', $event->id)
                ->where('status', 'confirmed')
                ->sum('price'),
        ];

        // Get recent bookings
        $recentBookings = Booking::where('accommodation_id', $event->id)
            ->with(['hotel', 'package', 'voucher', 'flight'])
            ->latest()
            ->take(10)
            ->get();

        // Get flights count
        $flightsCount = \App\Models\Flight::where('accommodation_id', $event->id)->count();

        return view('organizer.dashboard', compact('event', 'stats', 'recentBookings', 'flightsCount'));
    }

    /**
     * Display bookings for the organizer's event.
     */
    public function bookings()
    {
        $organizer = Auth::user();
        $event = $organizer->organizedAccommodations()->first();

        if (!$event) {
            return view('organizer.no-event');
        }

        $bookings = Booking::where('accommodation_id', $event->id)
            ->with(['hotel', 'package', 'voucher', 'flight'])
            ->latest()
            ->paginate(15);

        return view('organizer.bookings', compact('event', 'bookings'));
    }

    /**
     * Display flights for the organizer's event.
     */
    public function flights()
    {
        $organizer = Auth::user();
        $event = $organizer->organizedAccommodations()->first();

        if (!$event) {
            return view('organizer.no-event');
        }

        $flights = \App\Models\Flight::where('accommodation_id', $event->id)
            ->with(['user', 'booking'])
            ->latest()
            ->paginate(15);

        return view('organizer.flights', compact('event', 'flights'));
    }

    /**
     * Download voucher for a booking.
     */
    public function downloadVoucher(Booking $booking)
    {
        $organizer = Auth::user();
        $event = $organizer->organizedAccommodations()->first();

        // Verify booking belongs to organizer's event
        if (!$event || $booking->accommodation_id !== $event->id) {
            abort(403, 'You do not have permission to access this voucher.');
        }

        $voucher = $booking->voucher;

        if (!$voucher) {
            return back()->with('error', 'Voucher not found for this booking.');
        }

        // Check if PDF exists
        if ($voucher->pdf_path && file_exists(public_path('storage/' . $voucher->pdf_path))) {
            return response()->file(public_path('storage/' . $voucher->pdf_path));
        }

        // Generate PDF if it doesn't exist
        try {
            $booking->loadMissing(['accommodation', 'hotel', 'package', 'user', 'flight']);
            
            $pdf = Pdf::loadView('vouchers.template', compact('booking', 'voucher'));
            
            DualStorageService::makeDirectory('vouchers');
            $relativePath = "vouchers/{$voucher->id}.pdf";
            DualStorageService::put($relativePath, $pdf->output(), 'public');
            
            $voucher->update(['pdf_path' => $relativePath]);
            
            return $pdf->download("voucher-{$voucher->voucher_number}.pdf");
        } catch (\Throwable $e) {
            \Log::error('Failed to generate voucher PDF', [
                'voucher_id' => $voucher->id,
                'error' => $e->getMessage(),
            ]);
            
            return back()->with('error', 'Failed to generate voucher PDF.');
        }
    }
}
