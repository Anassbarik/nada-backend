<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class VoucherMail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre Bon de Confirmation - ' . ($this->booking->booking_reference ?? ''),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.voucher',
            with: [
                'booking' => $this->booking,
                'voucher' => $this->booking->voucher,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        if ($this->booking->voucher && $this->booking->voucher->pdf_path) {
            $filePath = Storage::disk('public')->path($this->booking->voucher->pdf_path);
            
            if (file_exists($filePath)) {
                $attachments[] = Attachment::fromPath($filePath)
                    ->as($this->booking->voucher->voucher_number . '.pdf')
                    ->withMime('application/pdf');
            }
        }

        return $attachments;
    }
}
