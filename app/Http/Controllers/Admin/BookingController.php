<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings.
     */
    public function index(Request $request)
    {
        $query = Booking::with(['event', 'hotel', 'package'])->latest();

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('event_id') && $request->event_id !== '') {
            $query->where('event_id', $request->event_id);
        }

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_reference', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('guest_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('guest_email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('guest_phone', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('flight_number', 'like', "%{$search}%");
            });
        }

        $bookings = $query->paginate(20);
        $events = \App\Models\Event::all();

        return view('admin.bookings.index', compact('bookings', 'events'));
    }

    /**
     * Update booking status.
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
        ]);

        // Use database transaction to ensure data consistency
        // The Booking model's updating event will automatically handle room count updates
        DB::transaction(function () use ($booking, $validated) {
            $booking->status = $validated['status'];
            $booking->save(); // Model event will handle package room count update
        });

        return redirect()->route('admin.bookings.index')->with('success', 'Booking status updated successfully.');
    }

    /**
     * Delete a booking.
     */
    public function destroy(Booking $booking)
    {
        // Use database transaction to ensure data consistency
        // The Booking model's deleting event will automatically handle room count updates
        DB::transaction(function () use ($booking) {
            $booking->delete(); // Model event will handle package room count update
        });

        return redirect()->route('admin.bookings.index')->with('success', 'Booking deleted successfully.');
    }
}
