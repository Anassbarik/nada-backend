<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bon de Confirmation</title>
    <style>
        @page { margin: 10mm; size: A4; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; margin: 0; padding: 0; line-height: 1.3; }
        .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
        .title { font-size: 18px; font-weight: 700; color: #059669; margin: 0; }
        .muted { color: #6b7280; font-size: 9px; }
        .box { border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th, td { text-align: left; padding: 4px 6px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        th { background: #f9fafb; font-weight: 600; font-size: 9px; }
        .right { text-align: right; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 9px; }
        .badge-paid { background: #dcfce7; color: #166534; }
        h3 { margin: 0 0 6px 0; font-size: 11px; }
        .no-page-break { page-break-inside: avoid; }
        .compact { margin: 0; padding: 0; }
    </style>
</head>
<body>
    @php
        $ttc = (float) ($booking->price ?? 0);
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

    <div class="header no-page-break">
        <div>
            <div class="title">Bon de Confirmation</div>
            <div class="muted">N°: {{ $voucher->voucher_number }}</div>
            <div class="muted">Réservation: {{ $booking->booking_reference ?? $booking->id }}</div>
        </div>
        <div class="right">
            <img src="{{ public_path('assets/logo-seminaireexpo.png') }}" alt="Logo" style="height: 35px;">
            <div class="muted" style="margin-top: 4px;">{{ now()->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="box no-page-break" style="margin-bottom: 8px;">
        <table>
            <tr>
                <td style="width: 60%;">
                    <strong style="font-size: 10px;">Client</strong><br>
                    <span style="font-size: 9px;">{{ $booking->full_name ?? $booking->guest_name ?? '—' }}</span><br>
                    <span class="muted" style="font-size: 8px;">{{ $booking->email ?? $booking->guest_email ?? '—' }}</span><br>
                    <span class="muted" style="font-size: 8px;">{{ $booking->phone ?? $booking->guest_phone ?? '—' }}</span>
                </td>
                <td class="right">
                    <strong>Statut</strong><br>
                    <span class="badge badge-paid">PAYÉ</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="box no-page-break">
        <h3 style="margin: 0 0 6px 0; font-size: 10px;">Détails de la réservation</h3>
        <table style="font-size: 9px;">
            <tr>
                <th style="width: 35%; font-size: 9px;">Champ</th>
                <th style="font-size: 9px;">Valeur</th>
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
                <td>Dates</td>
                <td>{{ $booking->checkin_date?->format('d/m/Y') ?? '—' }} - {{ $booking->checkout_date?->format('d/m/Y') ?? '—' }}</td>
            </tr>
            <tr>
                <td>Personnes</td>
                <td>{{ $booking->guests_count ?? '—' }}</td>
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
                        - {{ $booking->flight->departure_date ? \Carbon\Carbon::parse($booking->flight->departure_date)->format('d/m/Y') : '—' }} à {{ $booking->flight->departure_time ? \Carbon\Carbon::parse($booking->flight->departure_time)->format('H:i') : '' }}
                    @endif
                    @if($booking->flight->departure_airport || $booking->flight->arrival_airport)
                        <br><span class="text-gray-500 text-xs">{{ $booking->flight->departure_airport ?? '—' }} → {{ $booking->flight->arrival_airport ?? '—' }}</span>
                    @endif
                </td>
            </tr>
            @if($booking->flight->flight_category === 'round_trip' && $booking->flight->return_flight_number)
            <tr>
                <td>Vol Retour</td>
                <td>
                    {{ $booking->flight->return_flight_number }}
                    @if($booking->flight->return_date)
                        - {{ $booking->flight->return_date ? \Carbon\Carbon::parse($booking->flight->return_date)->format('d/m/Y') : '—' }} à {{ $booking->flight->return_departure_time ? \Carbon\Carbon::parse($booking->flight->return_departure_time)->format('H:i') : '' }}
                    @endif
                    @if($booking->flight->return_departure_airport || $booking->flight->return_arrival_airport)
                        <br><span class="text-gray-500 text-xs">{{ $booking->flight->return_departure_airport ?? '—' }} → {{ $booking->flight->return_arrival_airport ?? '—' }}</span>
                    @endif
                </td>
            </tr>
            @endif
            @elseif($booking->flight_number || $booking->flight_date || $booking->airport)
            <tr>
                <td>Vol</td>
                <td>
                    {{ $booking->flight_number ?? '' }}
                    @if($booking->flight_date) - {{ $booking->flight_date?->format('d/m/Y') ?? '' }} @endif
                    @if($booking->flight_time) {{ $booking->flight_time?->format('H:i') }} @endif
                    @if($booking->airport) - {{ $booking->airport }} @endif
                </td>
            </tr>
            @endif
        </table>
    </div>

    <div style="margin-top: 8px;" class="box no-page-break">
        <table style="font-size: 9px;">
            <tr>
                <th style="font-size: 9px;">Description</th>
                <th class="right" style="font-size: 9px;">Montant</th>
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
                <td style="font-size: 8px;">Hébergement (Package Hôtel)</td>
                <td class="right" style="font-size: 9px;">{{ number_format($hotelPrice, 2, '.', ' ') }} MAD</td>
            </tr>
            @endif
            
            @if($flightPrice > 0)
            <tr>
                <td style="font-size: 8px;">Vol Aller{{ $booking->flight && $booking->flight->flight_category === 'round_trip' ? ' + Retour' : '' }}</td>
                <td class="right" style="font-size: 9px;">
                    @if($booking->flight && $booking->flight->flight_category === 'round_trip')
                        {{ number_format($departurePrice, 2, '.', ' ') }} + {{ number_format($returnPrice, 2, '.', ' ') }} = {{ number_format($flightPrice, 2, '.', ' ') }} MAD
                    @else
                        {{ number_format($flightPrice, 2, '.', ' ') }} MAD
                    @endif
                </td>
            </tr>
            @endif
            
            <tr>
                <td class="right muted" style="font-size: 8px; padding-top: 6px;">Total HT</td>
                <td class="right" style="font-size: 9px; padding-top: 6px;">{{ number_format($ht, 2, '.', ' ') }} MAD</td>
            </tr>
            <tr>
                <td class="right muted" style="font-size: 8px;">TVA (20%)</td>
                <td class="right" style="font-size: 9px;">{{ number_format($tva, 2, '.', ' ') }} MAD</td>
            </tr>
            <tr>
                <th class="right" style="font-size: 11px; padding-top: 6px; padding-bottom: 6px;">Total TTC</th>
                <th class="right" style="font-size: 11px; padding-top: 6px; padding-bottom: 6px;">{{ number_format($ttc, 2, '.', ' ') }} MAD</th>
            </tr>
            <tr>
                <td colspan="2" class="muted" style="border-bottom: 0; padding-top: 6px; font-size: 8px; line-height: 1.2;">
                    <strong>Arrêté le présent bon à la somme de :</strong><br>
                    {{ $totalInLetters }} TTC
                </td>
            </tr>
        </table>
    </div>

    @if($booking->special_instructions || $booking->special_requests)
        <div style="margin-top: 8px;" class="box no-page-break">
            <strong style="font-size: 9px;">Instructions spéciales</strong><br>
            <div class="muted" style="white-space: pre-wrap; font-size: 8px; line-height: 1.2;">{{ $booking->special_instructions ?? $booking->special_requests }}</div>
        </div>
    @endif

    <div style="margin-top: 8px; padding: 8px; background: #f0fdf4; border-radius: 6px;" class="no-page-break">
        <strong style="color: #059669; font-size: 9px;">✓ Réservation confirmée et payée</strong><br>
        <div class="muted" style="margin-top: 2px; font-size: 8px; line-height: 1.2;">
            Ce bon de confirmation confirme que votre réservation a été payée et est confirmée. Veuillez présenter ce document lors de votre arrivée.
        </div>
    </div>
</body>
</html>

