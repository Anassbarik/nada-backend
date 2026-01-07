<x-mail::message>
# Votre Facture

Bonjour,

Veuillez trouver ci-joint votre **facture** pour la réservation **{{ $invoice->invoice_number }}**.

## Détails

**Référence réservation:** {{ $booking->booking_reference ?? $booking->id }}  
**Montant total:** {{ number_format((float) $invoice->total_amount, 2, '.', '') }} MAD  
**Statut:** {{ strtoupper($invoice->status ?? 'draft') }}

Merci,<br>
{{ config('app.name') }}
</x-mail::message>


