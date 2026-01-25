<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de Réservation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #059669; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0;">Confirmation de Réservation</h1>
    </div>

    <div style="background: #f9fafb; padding: 20px; border: 1px solid #e5e7eb; border-top: none;">
        <p>Bonjour {{ $booking->full_name ?? $booking->guest_name ?? 'Cher client' }},</p>

        <p>Nous avons bien reçu votre réservation et vous en remercions. Votre demande est en cours de traitement.</p>

        <div style="background: white; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #059669;">
            <h3 style="margin-top: 0; color: #059669;">Détails de votre réservation</h3>
            <p style="margin: 5px 0;"><strong>Référence:</strong> {{ $booking->booking_reference ?? $booking->id }}</p>
            <p style="margin: 5px 0;"><strong>Statut:</strong> 
                @if($booking->status === 'confirmed')
                    <span style="color: #059669; font-weight: bold;">Confirmée</span>
                @elseif($booking->status === 'paid')
                    <span style="color: #059669; font-weight: bold;">Payée</span>
                @else
                    <span style="color: #f59e0b; font-weight: bold;">En attente</span>
                @endif
            </p>
            <p style="margin: 5px 0;"><strong>Événement:</strong> {{ $booking->event->name ?? '—' }}</p>
            <p style="margin: 5px 0;"><strong>Hôtel:</strong> {{ $booking->hotel->name ?? '—' }}</p>
            <p style="margin: 5px 0;"><strong>Package:</strong> {{ $booking->package->nom_package ?? '—' }}</p>
            <p style="margin: 5px 0;"><strong>Type de chambre:</strong> {{ $booking->package->type_chambre ?? '—' }}</p>
            <p style="margin: 5px 0;"><strong>Date d'arrivée:</strong> {{ $booking->checkin_date?->format('d/m/Y') ?? '—' }}</p>
            <p style="margin: 5px 0;"><strong>Date de départ:</strong> {{ $booking->checkout_date?->format('d/m/Y') ?? '—' }}</p>
            <p style="margin: 5px 0;"><strong>Nombre de personnes:</strong> {{ $booking->guests_count ?? '—' }}</p>
            <p style="margin: 5px 0;"><strong>Montant total:</strong> {{ number_format((float) ($booking->price ?? 0), 2, ',', ' ') }} MAD</p>
        </div>

        @if($booking->payment_type === 'wallet' || $booking->payment_type === 'both')
            <div style="background: #dcfce7; padding: 15px; border-radius: 8px; margin: 20px 0;">
                <p style="margin: 0; color: #166534; font-weight: bold;">💳 Paiement effectué</p>
                @if($booking->payment_type === 'both')
                    <p style="margin: 5px 0; color: #166534;">
                        Montant payé via portefeuille: {{ number_format((float) ($booking->wallet_amount ?? 0), 2, ',', ' ') }} MAD<br>
                        Montant restant à payer: {{ number_format((float) ($booking->bank_amount ?? 0), 2, ',', ' ') }} MAD
                    </p>
                @else
                    <p style="margin: 5px 0; color: #166534;">Votre réservation a été payée intégralement via votre portefeuille.</p>
                @endif
            </div>
        @else
            <div style="background: #fef3c7; padding: 15px; border-radius: 8px; margin: 20px 0;">
                <p style="margin: 0; color: #92400e; font-weight: bold;">⏳ Paiement en attente</p>
                <p style="margin: 5px 0; color: #92400e;">Votre réservation sera confirmée une fois le paiement reçu.</p>
            </div>
        @endif

        @if($booking->flight_number || $booking->flight_date || $booking->airport)
            <div style="background: white; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #3b82f6;">
                <h3 style="margin-top: 0; color: #3b82f6;">Informations de vol</h3>
                @if($booking->flight_number)
                    <p style="margin: 5px 0;"><strong>Numéro de vol:</strong> {{ $booking->flight_number }}</p>
                @endif
                @if($booking->flight_date)
                    <p style="margin: 5px 0;"><strong>Date de vol:</strong> {{ $booking->flight_date->format('d/m/Y') }}</p>
                @endif
                @if($booking->flight_time)
                    <p style="margin: 5px 0;"><strong>Heure de vol:</strong> {{ $booking->flight_time->format('H:i') }}</p>
                @endif
                @if($booking->airport)
                    <p style="margin: 5px 0;"><strong>Aéroport:</strong> {{ $booking->airport }}</p>
                @endif
            </div>
        @endif

        @if($booking->special_instructions || $booking->special_requests)
            <div style="background: white; padding: 15px; border-radius: 8px; margin: 20px 0;">
                <h3 style="margin-top: 0; color: #059669;">Instructions spéciales</h3>
                <p style="margin: 0;">{{ $booking->special_instructions ?? $booking->special_requests }}</p>
            </div>
        @endif

        <p>Nous vous contacterons prochainement pour finaliser les détails de votre réservation. En attendant, vous pouvez consulter le statut de votre réservation dans votre espace personnel.</p>

        <p style="margin-top: 30px;">
            Cordialement,<br>
            <strong>L'équipe {{ config('app.name') }}</strong>
        </p>
    </div>

    <div style="background: #f3f4f6; padding: 15px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px; color: #6b7280;">
        <p style="margin: 0;">Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
        <p style="margin: 5px 0;">Pour toute question, veuillez contacter notre service client.</p>
    </div>
</body>
</html>






