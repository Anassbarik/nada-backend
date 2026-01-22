<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Services\DualStorageService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('booking')->latest()->paginate(20);

        return view('admin.invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('booking.event', 'booking.hotel', 'booking.package');

        return view('admin.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load('booking');

        return view('admin.invoices.edit', compact('invoice'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        // Check ownership
        if (!$invoice->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to edit this invoice.');
        }

        $validated = $request->validate([
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:draft,sent,paid',
            'notes' => 'nullable|string',
        ]);

        $invoice->update($validated);

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice)
    {
        // Check ownership
        if (!$invoice->canBeDeletedBy(auth()->user())) {
            abort(403, 'You do not have permission to delete this invoice.');
        }

        $invoice->delete();

        return redirect()->route('admin.invoices.index')->with('success', 'Invoice deleted successfully.');
    }

    public function stream(Request $request, Invoice $invoice)
    {
        $invoice->load('booking.event', 'booking.hotel', 'booking.package', 'booking.flight');

        $booking = $invoice->booking;
        $pdf = Pdf::loadView('invoices.template', compact('booking', 'invoice'));

        // If refresh=1, overwrite the stored PDF so existing PDFs get the latest template too.
        if ($request->boolean('refresh')) {
            DualStorageService::makeDirectory('invoices');
            $relativePath = $invoice->pdf_path ?: "invoices/{$invoice->id}.pdf";
            DualStorageService::put($relativePath, $pdf->output(), 'public');
            if ($invoice->pdf_path !== $relativePath) {
                $invoice->update(['pdf_path' => $relativePath]);
            }

            return response()->file(public_path('storage/' . $relativePath));
        }

        // Default behavior: serve existing stored PDF if present; otherwise stream generated PDF.
        if ($invoice->pdf_path && file_exists(public_path('storage/' . $invoice->pdf_path))) {
            return response()->file(public_path('storage/' . $invoice->pdf_path));
        }

        return $pdf->stream("facture-{$invoice->invoice_number}.pdf");
    }

    public function send(Invoice $invoice)
    {
        $invoice->load('booking.event', 'booking.hotel', 'booking.package', 'booking.flight');

        $booking = $invoice->booking;
        $to = $booking?->guest_email ?: $booking?->email;

        if (empty($to)) {
            return back()->with('error', 'No guest email found for this booking.');
        }

        $pdf = Pdf::loadView('invoices.template', compact('booking', 'invoice'));
        $pdfData = $pdf->output();

        Mail::to($to)->send(new InvoiceMail($invoice, $booking, $pdfData));

        $invoice->update(['status' => 'sent']);

        return back()->with('success', 'Facture envoyée.');
    }
}


