<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bon de Confirmation</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
        .title { font-size: 22px; font-weight: 700; color: #059669; }
        .muted { color: #6b7280; }
        .box { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        th { background: #f9fafb; font-weight: 600; }
        .right { text-align: right; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 999px; font-size: 11px; }
        .badge-paid { background: #dcfce7; color: #166534; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="title">Bon de Confirmation</div>
            <div class="muted">N°: {{ $voucher->voucher_number }}</div>
            <div class="muted">Réservation: {{ $booking->booking_reference ?? $booking->id }}</div>
        </div>
        <div class="right">
            <img src="{{ public_path('assets/logo-seminaireexpo.png') }}" alt="Logo" style="height: 46px;">
            <div class="muted" style="margin-top: 6px;">{{ now()->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="box" style="margin-bottom: 14px;">
        <table>
            <tr>
                <td>
                    <strong>Client</strong><br>
                    {{ $booking->full_name ?? $booking->guest_name ?? '—' }}<br>
                    <span class="muted">{{ $booking->email ?? $booking->guest_email ?? '—' }}</span><br>
                    <span class="muted">{{ $booking->phone ?? $booking->guest_phone ?? '—' }}</span>
                </td>
                <td class="right">
                    <strong>Statut</strong><br>
                    <span class="badge badge-paid">PAYÉ</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="box">
        <h3 style="margin: 0 0 10px 0;">Détails de la réservation</h3>
        <table>
            <tr>
                <th>Champ</th>
                <th>Valeur</th>
            </tr>
            <tr>
                <td>Événement</td>
                <td>{{ $booking->event->name ?? '—' }}</td>
            </tr>
            <tr>
                <td>Hôtel</td>
                <td>{{ $booking->hotel->name ?? '—' }}</td>
            </tr>
            <tr>
                <td>Package</td>
                <td>{{ $booking->package->nom_package ?? '—' }}</td>
            </tr>
            <tr>
                <td>Type de chambre</td>
                <td>{{ $booking->package->type_chambre ?? '—' }}</td>
            </tr>
            <tr>
                <td>Date d'arrivée</td>
                <td>{{ $booking->checkin_date?->format('d/m/Y') ?? '—' }}</td>
            </tr>
            <tr>
                <td>Date de départ</td>
                <td>{{ $booking->checkout_date?->format('d/m/Y') ?? '—' }}</td>
            </tr>
            <tr>
                <td>Nombre de personnes</td>
                <td>{{ $booking->guests_count ?? '—' }}</td>
            </tr>
            @if($booking->flight_number)
            <tr>
                <td>Numéro de vol</td>
                <td>{{ $booking->flight_number }}</td>
            </tr>
            @endif
            @if($booking->flight_date)
            <tr>
                <td>Date/Heure de vol</td>
                <td>
                    {{ $booking->flight_date?->format('d/m/Y') ?? '—' }}
                    {{ $booking->flight_time?->format('H:i') ?? '' }}
                </td>
            </tr>
            @endif
            @if($booking->airport)
            <tr>
                <td>Aéroport</td>
                <td>{{ $booking->airport }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div style="margin-top: 16px;" class="box">
        <table>
            <tr>
                <th>Description</th>
                <th class="right">Montant</th>
            </tr>
            <tr>
                <td>Réservation confirmée</td>
                <td class="right">{{ number_format((float) ($booking->price ?? 0), 2, ',', ' ') }} €</td>
            </tr>
            <tr>
                <th class="right">Total</th>
                <th class="right">{{ number_format((float) ($booking->price ?? 0), 2, ',', ' ') }} €</th>
            </tr>
        </table>
    </div>

    @if($booking->special_instructions || $booking->special_requests)
        <div style="margin-top: 16px;" class="box">
            <strong>Instructions spéciales</strong><br>
            <div class="muted" style="white-space: pre-wrap;">{{ $booking->special_instructions ?? $booking->special_requests }}</div>
        </div>
    @endif

    <div style="margin-top: 20px; padding: 12px; background: #f0fdf4; border-radius: 8px;">
        <strong style="color: #059669;">✓ Réservation confirmée et payée</strong><br>
        <div class="muted" style="margin-top: 4px;">
            Ce bon de confirmation confirme que votre réservation a été payée et est confirmée.
            Veuillez présenter ce document lors de votre arrivée.
        </div>
    </div>
</body>
</html>

