<?php

namespace App\Console\Commands;

use App\Models\Voucher;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RegenerateVoucherPdfs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vouchers:regenerate-pdfs {--voucher_id= : Regenerate only a specific voucher id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate and overwrite voucher (Bon de Confirmation) PDF files using the latest vouchers.template';

    public function handle(): int
    {
        $voucherId = $this->option('voucher_id');

        $query = Voucher::query()->with(['booking.event', 'booking.hotel', 'booking.package', 'booking.flight', 'user']);

        if (!empty($voucherId)) {
            $query->whereKey($voucherId);
        }

        $vouchers = $query->get();

        if ($vouchers->isEmpty()) {
            $this->warn($voucherId ? "No voucher found for id={$voucherId}." : 'No vouchers found.');
            return self::SUCCESS;
        }

        Storage::disk('public')->makeDirectory('vouchers');

        $ok = 0;
        $failed = 0;

        foreach ($vouchers as $voucher) {
            try {
                $booking = $voucher->booking;
                if (!$booking) {
                    $this->warn("Voucher #{$voucher->id} has no booking; skipping.");
                    $failed++;
                    continue;
                }

                $relativePath = $voucher->pdf_path ?: "vouchers/{$voucher->id}.pdf";

                $pdf = Pdf::loadView('vouchers.template', [
                    'booking' => $booking,
                    'voucher' => $voucher,
                ]);

                Storage::disk('public')->put($relativePath, $pdf->output());

                if ($voucher->pdf_path !== $relativePath) {
                    $voucher->update(['pdf_path' => $relativePath]);
                }

                $this->info("✓ Regenerated voucher #{$voucher->id} -> {$relativePath}");
                $ok++;
            } catch (\Throwable $e) {
                $this->error("✗ Failed voucher #{$voucher->id}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Done. Success: {$ok}, Failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}


