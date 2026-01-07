<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RegenerateInvoicePdfs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:regenerate-pdfs {--invoice_id= : Regenerate only a specific invoice id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate and overwrite invoice PDF files using the latest invoices.template';

    public function handle(): int
    {
        $invoiceId = $this->option('invoice_id');

        $query = Invoice::query()->with(['booking.event', 'booking.hotel', 'booking.package']);

        if (!empty($invoiceId)) {
            $query->whereKey($invoiceId);
        }

        $invoices = $query->get();

        if ($invoices->isEmpty()) {
            $this->warn($invoiceId ? "No invoice found for id={$invoiceId}." : 'No invoices found.');
            return self::SUCCESS;
        }

        Storage::disk('public')->makeDirectory('invoices');

        $ok = 0;
        $failed = 0;

        foreach ($invoices as $invoice) {
            try {
                $booking = $invoice->booking;
                if (!$booking) {
                    $this->warn("Invoice #{$invoice->id} has no booking; skipping.");
                    $failed++;
                    continue;
                }

                $relativePath = $invoice->pdf_path ?: "invoices/{$invoice->id}.pdf";

                $pdf = Pdf::loadView('invoices.template', [
                    'booking' => $booking,
                    'invoice' => $invoice,
                ]);

                Storage::disk('public')->put($relativePath, $pdf->output());

                if ($invoice->pdf_path !== $relativePath) {
                    $invoice->update(['pdf_path' => $relativePath]);
                }

                $this->info("✓ Regenerated invoice #{$invoice->id} -> {$relativePath}");
                $ok++;
            } catch (\Throwable $e) {
                $this->error("✗ Failed invoice #{$invoice->id}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Done. Success: {$ok}, Failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}


