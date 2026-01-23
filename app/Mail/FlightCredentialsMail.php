<?php

namespace App\Mail;

use App\Models\Flight;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FlightCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public Flight $flight;
    public User $user;
    public string $password;
    public $booking;

    /**
     * Create a new message instance.
     */
    public function __construct(Flight $flight, User $user, string $password, $booking = null)
    {
        $this->flight = $flight;
        $this->user = $user;
        $this->password = $password;
        $this->booking = $booking;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Flight Booking Credentials - ' . ($this->flight->reference ?? 'N/A'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.flight-credentials',
            with: [
                'flight' => $this->flight,
                'user' => $this->user,
                'password' => $this->password,
                'booking' => $this->booking,
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
        
        // Attach credentials PDF if exists
        if ($this->flight->credentials_pdf_path) {
            $path = storage_path('app/public/' . $this->flight->credentials_pdf_path);
            if (!file_exists($path)) {
                $path = public_path('storage/' . $this->flight->credentials_pdf_path);
            }
            
            if (file_exists($path)) {
                $attachments[] = \Illuminate\Mail\Mailables\Attachment::fromPath($path)
                    ->as('flight-credentials-' . $this->flight->reference . '.pdf');
            }
        }
        
        return $attachments;
    }
}
