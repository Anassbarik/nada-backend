<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VoucherController extends Controller
{
    /**
     * Get authenticated user's vouchers (only paid bookings).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $vouchers = Voucher::where('user_id', $user->id)
            ->whereHas('booking', function ($query) {
                $query->where('status', 'paid');
            })
            ->with(['booking.event', 'booking.hotel', 'booking.package'])
            ->latest()
            ->get();

        $formattedVouchers = $vouchers->map(function ($voucher) {
            return [
                'id' => $voucher->id,
                'voucher_number' => $voucher->voucher_number,
                'emailed' => $voucher->emailed,
                'booking' => [
                    'id' => $voucher->booking->id,
                    'booking_reference' => $voucher->booking->booking_reference,
                    'status' => $voucher->booking->status,
                    'full_name' => $voucher->booking->full_name ?? $voucher->booking->guest_name,
                    'email' => $voucher->booking->email ?? $voucher->booking->guest_email,
                    'checkin_date' => $voucher->booking->checkin_date?->format('Y-m-d'),
                    'checkout_date' => $voucher->booking->checkout_date?->format('Y-m-d'),
                    'price' => $voucher->booking->price,
                    'event' => $voucher->booking->event ? [
                        'id' => $voucher->booking->event->id,
                        'name' => $voucher->booking->event->name,
                        'slug' => $voucher->booking->event->slug,
                    ] : null,
                    'hotel' => $voucher->booking->hotel ? [
                        'id' => $voucher->booking->hotel->id,
                        'name' => $voucher->booking->hotel->name,
                        'slug' => $voucher->booking->hotel->slug,
                    ] : null,
                ],
                'pdf_url' => $voucher->pdf_path ? Storage::url($voucher->pdf_path) : null,
                'created_at' => $voucher->created_at?->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedVouchers,
        ]);
    }

    /**
     * Download voucher PDF.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Voucher  $voucher
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Voucher $voucher)
    {
        // Verify ownership and paid status
        if ($voucher->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized access to this voucher.');
        }

        if ($voucher->booking->status !== 'paid') {
            abort(403, 'Voucher is only available for paid bookings.');
        }

        if (!$voucher->pdf_path || !Storage::disk('public')->exists($voucher->pdf_path)) {
            abort(404, 'Voucher PDF not found.');
        }

        $filePath = Storage::disk('public')->path($voucher->pdf_path);

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $voucher->voucher_number . '.pdf"',
        ]);
    }
}
