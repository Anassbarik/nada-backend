<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\VoucherMail;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings.
     */
    public function index(Request $request)
    {
        $query = Booking::with(['event', 'hotel', 'package', 'invoice'])->latest();

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
            'status' => 'required|in:pending,confirmed,paid,cancelled,refunded',
        ]);

        // Use database transaction to ensure data consistency
        DB::transaction(function () use ($booking, $validated) {
            $oldStatus = $booking->status;
            $booking->status = $validated['status'];
            $booking->save(); // Model event will handle package room count update

            // If status changed to 'paid' and voucher exists, email it to user
            if ($validated['status'] === 'paid' && $oldStatus !== 'paid' && $booking->voucher) {
                try {
                    $booking->loadMissing(['user', 'voucher', 'event', 'hotel', 'package']);
                    
                    if ($booking->user && $booking->user->email) {
                        Mail::to($booking->user->email)
                            ->send(new VoucherMail($booking));
                        
                        $booking->voucher->update(['emailed' => true]);
                    }
                } catch (\Throwable $e) {
                    Log::error('Failed to send voucher email', [
                        'booking_id' => $booking->id,
                        'error_message' => $e->getMessage(),
                    ]);
                    // Don't fail the status update if email fails
                }
            }
        });

        return redirect()->route('admin.bookings.index')->with('success', 'Statut mis à jour avec succès.');
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

    /**
     * Process a refund for a booking.
     */
    public function refund(Request $request, Booking $booking)
    {
        // Validate that booking can be refunded
        if ($booking->status === 'refunded') {
            return redirect()->route('admin.bookings.index')
                ->with('error', 'Cette réservation a déjà été remboursée.');
        }

        // Get the total price (use booking price or package price)
        $totalPrice = $booking->price ?? ($booking->package->prix_ttc ?? 0);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0', 'max:' . $totalPrice],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Use database transaction to ensure data consistency
        DB::transaction(function () use ($booking, $validated) {
            $booking->update([
                'status' => 'refunded',
                'refund_amount' => $validated['amount'],
                'refund_notes' => $validated['notes'] ?? null,
                'refunded_at' => now(),
            ]);
            // The Booking model's updating event will automatically handle room count updates
        });

        // Optional: Log refund action or integrate payment gateway refund API here
        // Future: Dispatch event for Sanctum user notification

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Remboursement traité avec succès.');
    }
}
