<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre Bon de Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #059669; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0;">Bon de Confirmation</h1>
    </div>

    <div style="background: #f9fafb; padding: 20px; border: 1px solid #e5e7eb; border-top: none;">
        <p>Bonjour {{ $booking->full_name ?? $booking->guest_name ?? 'Cher client' }},</p>

        <p>Nous avons le plaisir de vous confirmer que votre réservation a été payée et est maintenant confirmée.</p>

        <div style="background: white; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #059669;">
            <h3 style="margin-top: 0; color: #059669;">Détails de votre réservation</h3>
            <p style="margin: 5px 0;"><strong>Référence:</strong> {{ $booking->booking_reference ?? $booking->id }}</p>
            <p style="margin: 5px 0;"><strong>Bon de confirmation:</strong> {{ $voucher->voucher_number }}</p>
            <p style="margin: 5px 0;"><strong>Événement:</strong> {{ $booking->event->name ?? '—' }}</p>
            <p style="margin: 5px 0;"><strong>Hôtel:</strong> {{ $booking->hotel->name ?? '—' }}</p>
            <p style="margin: 5px 0;"><strong>Date d'arrivée:</strong> {{ $booking->checkin_date?->format('d/m/Y') ?? '—' }}</p>
            <p style="margin: 5px 0;"><strong>Date de départ:</strong> {{ $booking->checkout_date?->format('d/m/Y') ?? '—' }}</p>
            <p style="margin: 5px 0;"><strong>Montant:</strong> {{ number_format((float) ($booking->price ?? 0), 2, '.', '') }} MAD</p>
        </div>

        <p>Votre bon de confirmation en format PDF est joint à cet email. Veuillez le conserver et le présenter lors de votre arrivée.</p>

        <div style="background: #dcfce7; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center;">
            <p style="margin: 0; color: #166534; font-weight: bold;">✓ Réservation confirmée et payée</p>
        </div>

        <p>Nous vous remercions pour votre confiance et nous réjouissons de vous accueillir.</p>

        <p style="margin-top: 30px;">
            Cordialement,<br>
            <strong>L'équipe Séminaire Expo</strong>
        </p>
    </div>

    <div style="background: #f3f4f6; padding: 15px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px; color: #6b7280;">
        <p style="margin: 0;">Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
    </div>
</body>
</html>

