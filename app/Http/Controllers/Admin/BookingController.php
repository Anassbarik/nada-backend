<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\VoucherMail;
use App\Models\Booking;
use App\Models\Voucher;
use App\Services\DualStorageService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings.
     */
    public function index(Request $request)
    {
        $query = Booking::with(['accommodation', 'hotel', 'package', 'invoice'])->latest();

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('accommodation_id') && $request->accommodation_id !== '') {
            $query->where('accommodation_id', $request->accommodation_id);
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
        $accommodations = \App\Models\Accommodation::all();

        return view('admin.bookings.index', compact('bookings', 'accommodations'));
    }

    /**
     * Update booking status.
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        // Check ownership
        if (!$booking->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to edit this booking.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,paid,cancelled,refunded',
        ]);

        // Use database transaction to ensure data consistency
        DB::transaction(function () use ($booking, $validated) {
            // Ensure package is loaded for room availability restoration when status changes
            $booking->loadMissing('package');
            
            $oldStatus = $booking->status;
            $booking->status = $validated['status'];
            $booking->save(); // Model event will handle package room count update
            // When status changes to 'refunded' or 'cancelled', room availability is restored

            // If status changed to 'paid', generate voucher if missing and email it to user
            if ($validated['status'] === 'paid' && $oldStatus !== 'paid') {
                try {
                    $booking->loadMissing(['accommodation', 'hotel', 'package', 'user']);
                    
                    // Generate voucher if it doesn't exist
                    if (!$booking->voucher) {
                        $voucherNumber = 'VOC-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));
                        while (Voucher::where('voucher_number', $voucherNumber)->exists()) {
                            $voucherNumber = 'VOC-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));
                        }

                        $userId = $booking->user_id ?? null;
                        if (!$userId) {
                            Log::warning('Cannot create voucher: booking has no user_id', [
                                'booking_id' => $booking->id,
                            ]);
                        } else {
                            $voucher = $booking->voucher()->create([
                                'user_id' => $userId,
                                'voucher_number' => $voucherNumber,
                                'emailed' => false,
                            ]);

                            // Generate voucher PDF
                            $pdf = Pdf::loadView('vouchers.template', [
                                'booking' => $booking,
                                'voucher' => $voucher,
                            ]);
                            DualStorageService::makeDirectory('vouchers');
                            $relativePath = "vouchers/{$voucher->id}.pdf";
                            DualStorageService::put($relativePath, $pdf->output(), 'public');
                            $voucher->update(['pdf_path' => $relativePath]);
                            
                            $booking->load('voucher');
                        }
                    }
                    
                    // Send voucher email if voucher exists
                    if ($booking->voucher) {
                        $booking->loadMissing(['user', 'voucher', 'accommodation', 'hotel', 'package']);
                        
                        $email = $booking->user->email ?? $booking->email ?? $booking->guest_email ?? null;
                        if ($email) {
                            Mail::to($email)
                                ->send(new VoucherMail($booking));
                            
                            $booking->voucher->update(['emailed' => true]);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::error('Failed to generate voucher or send voucher email', [
                        'booking_id' => $booking->id,
                        'error_message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Don't fail the status update if voucher generation/email fails
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
        // Check ownership
        if (!$booking->canBeDeletedBy(auth()->user())) {
            abort(403, 'You do not have permission to delete this booking.');
        }

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
            // Ensure package is loaded for room availability restoration
            $booking->loadMissing('package');
            
            $booking->update([
                'status' => 'refunded',
                'refund_amount' => $validated['amount'],
                'refund_notes' => $validated['notes'] ?? null,
                'refunded_at' => now(),
            ]);
            // The Booking model's updating event will automatically handle room count updates
            // When status changes to 'refunded', it increments chambres_restantes and sets disponibilite
        });

        // Optional: Log refund action or integrate payment gateway refund API here
        // Future: Dispatch event for Sanctum user notification

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Remboursement traité avec succès.');
    }

    /**
     * Download payment document.
     */
    public function downloadPaymentDocument(Booking $booking)
    {
        if (!$booking->payment_document_path) {
            abort(404, 'Payment document not found.');
        }

        $path = storage_path('app/public/' . $booking->payment_document_path);
        
        // Also check public storage
        if (!file_exists($path)) {
            $path = public_path('storage/' . $booking->payment_document_path);
        }

        if (!file_exists($path)) {
            abort(404, 'Payment document file not found.');
        }

        $filename = 'ordre-paiement-booking-' . $booking->booking_reference . '.' . pathinfo($booking->payment_document_path, PATHINFO_EXTENSION);
        
        return response()->download($path, $filename);
    }

    /**
     * Download flight ticket.
     */
    public function downloadFlightTicket(Booking $booking)
    {
        if (!$booking->flight_ticket_path) {
            abort(404, 'Flight ticket not found.');
        }

        $path = storage_path('app/public/' . $booking->flight_ticket_path);
        
        // Also check public storage
        if (!file_exists($path)) {
            $path = public_path('storage/' . $booking->flight_ticket_path);
        }

        if (!file_exists($path)) {
            abort(404, 'Flight ticket file not found.');
        }

        $filename = 'billet-avion-booking-' . $booking->booking_reference . '.' . pathinfo($booking->flight_ticket_path, PATHINFO_EXTENSION);
        
        return response()->download($path, $filename);
    }
}
