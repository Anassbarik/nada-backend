<?php

namespace App\Console\Commands;

use App\Models\Accommodation;
use App\Models\Booking;
use App\Models\Flight;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateMissingFlightBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flights:create-missing-bookings {--only-clients : Only create bookings for flights with beneficiary_type=client}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure every flight has a corresponding booking row (for linking flights to future hotel bookings).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $onlyClients = (bool) $this->option('only-clients');

        $this->info('Scanning flights and creating missing bookings...');

        // Work on all flights that are linked to an accommodation.
        // We will apply the --only-clients filter per flight so we
        // never incorrectly say "no flights" when they exist.
        $flights = Flight::query()
            ->whereNotNull('accommodation_id')
            ->get();

        if ($flights->isEmpty()) {
            $this->info('No flights with events/accommodations found to process.');
            return self::SUCCESS;
        }

        $createdCount = 0;

        foreach ($flights as $flight) {
            // Apply optional filter: only client flights
            if ($onlyClients && $flight->beneficiary_type !== 'client') {
                continue;
            }

            // Skip if a booking already exists for this flight_id
            if (Booking::where('flight_id', $flight->id)->exists()) {
                continue;
            }
            DB::transaction(function () use (&$createdCount, $flight) {
                /** @var Accommodation|null $accommodation */
                $accommodation = $flight->accommodation;

                if (!$accommodation) {
                    $this->warn("Skipping flight {$flight->id} ({$flight->reference}) - no accommodation linked.");
                    return;
                }

                // Calculate total flight price
                $flightPrice = (float) ($flight->departure_price_ttc ?? 0);
                if ($flight->flight_category === 'round_trip' && $flight->return_price_ttc) {
                    $flightPrice += (float) $flight->return_price_ttc;
                }

                // Combine date and time for flight_time.
                // In some datasets departure_time is already a full datetime, so avoid double-specifying the date.
                $flightDateTime = null;
                if ($flight->departure_time) {
                    $flightDateTime = \Carbon\Carbon::parse($flight->departure_time);
                } elseif ($flight->departure_date) {
                    $flightDateTime = \Carbon\Carbon::parse($flight->departure_date);
                }

                $bookingData = [
                    'user_id' => $flight->user_id,
                    'created_by' => $flight->created_by,
                    'accommodation_id' => $flight->accommodation_id,
                    'flight_id' => $flight->id,
                    'hotel_id' => null,
                    'package_id' => null,
                    'full_name' => $flight->full_name,
                    'flight_number' => $flight->departure_flight_number,
                    'flight_date' => $flight->departure_date,
                    'flight_time' => $flightDateTime,
                    'status' => 'confirmed',
                    'price' => $flightPrice,
                    // For legacy non-nullable schema, use flight dates for checkin/checkout
                    'checkin_date' => $flight->departure_date ?? now()->toDateString(),
                    'checkout_date' => $flight->return_arrival_date
                        ?? $flight->arrival_date
                        ?? $flight->departure_date
                        ?? now()->toDateString(),
                    'guests_count' => 1,
                ];

                // Commission based on accommodation settings
                $commissionAmount = null;
                if ($accommodation->commission_percentage && $accommodation->commission_percentage > 0) {
                    $commissionAmount = round(($flightPrice * $accommodation->commission_percentage) / 100, 2);
                }
                $bookingData['commission_amount'] = $commissionAmount;

                // Guest info for later email linking
                // guest_email is required in DB, so provide fallback if missing
                $guestEmail = $flight->client_email;
                if (!$guestEmail && $flight->user_id) {
                    $flight->load('user');
                    $guestEmail = $flight->user->email ?? null;
                }
                if (!$guestEmail && $flight->beneficiary_type === 'organizer' && $flight->organizer_id) {
                    $flight->load('organizer');
                    $guestEmail = $flight->organizer->email ?? null;
                }
                // Last resort: generate placeholder email (DB requires non-null)
                if (!$guestEmail) {
                    $guestEmail = 'flight-' . strtolower($flight->reference) . '@noreply.local';
                }

                $bookingData['guest_name'] = $flight->full_name;
                $bookingData['guest_email'] = $guestEmail;
                $bookingData['email'] = $guestEmail;

                /** @var Booking $booking */
                $booking = Booking::create($bookingData);

                $this->line("Created booking {$booking->booking_reference} for flight {$flight->reference} (ID {$flight->id}).");
                $createdCount++;
            });
        }

        $this->info("Done. Created {$createdCount} booking(s) for flights without bookings.");

        return self::SUCCESS;
    }
}


