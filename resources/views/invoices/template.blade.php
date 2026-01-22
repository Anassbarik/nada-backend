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
    @php
        $ttc = (float) ($invoice->total_amount ?? 0);
        $taxRate = 0.20; // 20%
        $ht = $ttc > 0 ? ($ttc / (1 + $taxRate)) : 0.0;
        $tva = max(0.0, $ttc - $ht);

        /**
         * Convert a monetary amount to French words (e.g. "Deux cent dirhams et dix centimes").
         * Best-effort: uses intl NumberFormatter when available.
         */
        $amountToWordsFr = function (float $amount, string $currencyLabel = 'dirhams'): string {
            $amount = round($amount, 2);
            $whole = (int) floor($amount);
            $cents = (int) round(($amount - $whole) * 100);

            $spellout = null;
            try {
                if (class_exists(\NumberFormatter::class)) {
                    $spellout = new \NumberFormatter('fr_FR', \NumberFormatter::SPELLOUT);
                }
            } catch (\Throwable $e) {
                $spellout = null;
            }

            $wholeWords = $spellout ? (string) $spellout->format($whole) : (string) $whole;
            $centsWords = $spellout ? (string) $spellout->format($cents) : (string) $cents;

            $wholeWords = trim($wholeWords);
            $centsWords = trim($centsWords);

            $text = $wholeWords . ' ' . $currencyLabel . ' et ' . $centsWords . ' centimes';

            // Capitalize first letter (UTF-8 safe)
            if (function_exists('mb_substr') && function_exists('mb_strtoupper')) {
                return mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
            }

            return ucfirst($text);
        };

        $totalInLetters = $amountToWordsFr($ttc, 'dirhams');
    @endphp

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
            @if($booking->flight)
            <tr>
                <td>Type de vol</td>
                <td>{{ $booking->flight->flight_category_label ?? '—' }}</td>
            </tr>
            <tr>
                <td>Vol Aller</td>
                <td>
                    {{ $booking->flight->departure_flight_number ?? '—' }}
                    @if($booking->flight->departure_date)
                        - {{ $booking->flight->departure_date->format('d/m/Y') }} à {{ $booking->flight->departure_time ? \Carbon\Carbon::parse($booking->flight->departure_time)->format('H:i') : '' }}
                    @endif
                    @if($booking->flight->departure_airport || $booking->flight->arrival_airport)
                        <br><span class="text-gray-500 text-sm">{{ $booking->flight->departure_airport ?? '—' }} → {{ $booking->flight->arrival_airport ?? '—' }}</span>
                    @endif
                </td>
            </tr>
            @if($booking->flight->flight_category === 'round_trip' && $booking->flight->return_flight_number)
            <tr>
                <td>Vol Retour</td>
                <td>
                    {{ $booking->flight->return_flight_number }}
                    @if($booking->flight->return_date)
                        - {{ $booking->flight->return_date->format('d/m/Y') }} à {{ $booking->flight->return_departure_time ? \Carbon\Carbon::parse($booking->flight->return_departure_time)->format('H:i') : '' }}
                    @endif
                    @if($booking->flight->return_departure_airport || $booking->flight->return_arrival_airport)
                        <br><span class="text-gray-500 text-sm">{{ $booking->flight->return_departure_airport ?? '—' }} → {{ $booking->flight->return_arrival_airport ?? '—' }}</span>
                    @endif
                </td>
            </tr>
            @endif
            @elseif($booking->flight_number || $booking->flight_date)
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
            @endif
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
            @php
                $hotelPrice = 0;
                $flightPrice = 0;
                $departurePrice = 0;
                $returnPrice = 0;
                
                // Calculate hotel package price
                if ($booking->package && $booking->package->prix_ttc) {
                    $hotelPrice = (float) $booking->package->prix_ttc;
                }
                
                // Calculate flight prices
                if ($booking->flight) {
                    $departurePrice = (float) ($booking->flight->departure_price_ttc ?? 0);
                    if ($booking->flight->flight_category === 'round_trip' && $booking->flight->return_price_ttc) {
                        $returnPrice = (float) $booking->flight->return_price_ttc;
                    }
                    $flightPrice = $departurePrice + $returnPrice;
                }
            @endphp
            
            @if($hotelPrice > 0)
            <tr>
                <td>Hébergement (Package Hôtel)</td>
                <td class="right">{{ number_format($hotelPrice, 2, '.', ' ') }} MAD</td>
            </tr>
            @endif
            
            @if($flightPrice > 0)
            <tr>
                <td>Vol Aller{{ $booking->flight && $booking->flight->flight_category === 'round_trip' ? ' + Retour' : '' }}</td>
                <td class="right">
                    @if($booking->flight && $booking->flight->flight_category === 'round_trip')
                        {{ number_format($departurePrice, 2, '.', ' ') }} + {{ number_format($returnPrice, 2, '.', ' ') }} = {{ number_format($flightPrice, 2, '.', ' ') }} MAD
                    @else
                        {{ number_format($flightPrice, 2, '.', ' ') }} MAD
                    @endif
                </td>
            </tr>
            @endif
            
            <tr>
                <td class="right muted" style="padding-top: 8px;">Total HT</td>
                <td class="right" style="padding-top: 8px;">{{ number_format($ht, 2, '.', ' ') }} MAD</td>
            </tr>
            <tr>
                <td class="right muted">TVA (20%)</td>
                <td class="right">{{ number_format($tva, 2, '.', ' ') }} MAD</td>
            </tr>
            <tr>
                <th class="right" style="font-size: 15px; padding-top: 10px; padding-bottom: 10px;">Total TTC</th>
                <th class="right" style="font-size: 15px; padding-top: 10px; padding-bottom: 10px;">{{ number_format($ttc, 2, '.', ' ') }} MAD</th>
            </tr>
            <tr>
                <td colspan="2" class="muted" style="border-bottom: 0; padding-top: 10px;">
                    <strong>Arrêté la présente facture à la somme de :</strong>
                    {{ $totalInLetters }} TTC
                </td>
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


