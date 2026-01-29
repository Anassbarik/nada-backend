<?php

namespace App\Exports;

use App\Models\Flight;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FlightsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected ?int $accommodationId = null,
        protected ?int $flightId = null,
    ) {}

    public function collection(): Collection
    {
        $query = Flight::with(['accommodation', 'bookings']);

        if ($this->accommodationId) {
            $query->where('accommodation_id', $this->accommodationId);
        }

        if ($this->flightId) {
            $query->where('id', $this->flightId);
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'Reference',
            'Event Name',
            'Client Name',
            'Flight Class',
            'Flight Type',
            'Departure Date',
            'Departure Time',
            'Departure Airport',
            'Arrival Airport',
            'Return Date',
            'Return Departure Time',
            'Return Arrival Date',
            'Return Arrival Time',
            'Return Departure Airport',
            'Return Arrival Airport',
            'Status',
            'Payment Method',
            'Total Price (MAD)',
            'Booking Reference',
            'eTicket Number',
            'Ticket Reference (Airline Reference)',
            'Created At',
        ];
    }

    public function map($flight): array
    {
        // Get booking references (concatenate if multiple bookings)
        $bookingReferences = $flight->bookings
            ->pluck('booking_reference')
            ->filter()
            ->implode(', ');

        return [
            $flight->reference,
            $flight->accommodation?->name,
            $flight->full_name,
            $flight->flight_class_label,
            $flight->flight_category_label,
            optional($flight->departure_date)->format('Y-m-d'),
            $flight->departure_time ? \Carbon\Carbon::parse($flight->departure_time)->format('H:i') : null,
            $flight->departure_airport,
            $flight->arrival_airport,
            optional($flight->return_date)->format('Y-m-d'),
            $flight->return_departure_time ? \Carbon\Carbon::parse($flight->return_departure_time)->format('H:i') : null,
            optional($flight->return_arrival_date)->format('Y-m-d'),
            $flight->return_arrival_time ? \Carbon\Carbon::parse($flight->return_arrival_time)->format('H:i') : null,
            $flight->return_departure_airport,
            $flight->return_arrival_airport,
            $flight->status_label,
            $flight->payment_method_label,
            $flight->total_price,
            $bookingReferences ?: null,
            $flight->eticket,
            $flight->ticket_reference,
            optional($flight->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}


