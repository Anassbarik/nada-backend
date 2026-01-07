<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
        .title { font-size: 22px; font-weight: 700; }
        .muted { color: #6b7280; }
        .box { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        th { background: #f9fafb; font-weight: 600; }
        .right { text-align: right; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 999px; font-size: 11px; }
        .badge-draft { background: #f3f4f6; }
        .badge-sent { background: #dbeafe; }
        .badge-paid { background: #dcfce7; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="title">Facture</div>
            <div class="muted">N°: {{ $invoice->invoice_number }}</div>
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
                    @php($status = $invoice->status ?? 'draft')
                    <span class="badge badge-{{ $status }}">{{ strtoupper($status) }}</span>
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
                <td>Vol</td>
                <td>{{ $booking->flight_number ?? '—' }}</td>
            </tr>
            <tr>
                <td>Date/Heure de vol</td>
                <td>
                    {{ $booking->flight_date?->format('Y-m-d') ?? '—' }}
                    {{ $booking->flight_time?->format('H:i') ?? '' }}
                </td>
            </tr>
            <tr>
                <td>Email invité</td>
                <td>{{ $booking->guest_email ?? $booking->email ?? '—' }}</td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 16px;" class="box">
        <table>
            <tr>
                <th>Description</th>
                <th class="right">Montant</th>
            </tr>
            <tr>
                <td>Réservation</td>
                <td class="right">{{ number_format((float) $invoice->total_amount, 2, '.', '') }} MAD</td>
            </tr>
            <tr>
                <th class="right">Total</th>
                <th class="right">{{ number_format((float) $invoice->total_amount, 2, '.', '') }} MAD</th>
            </tr>
        </table>
    </div>

    @if(!empty($invoice->notes))
        <div style="margin-top: 16px;" class="box">
            <strong>Notes</strong><br>
            <div class="muted" style="white-space: pre-wrap;">{{ $invoice->notes }}</div>
        </div>
    @endif
</body>
</html>


