<?php

namespace App\Exports;

use App\Models\Transfer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransfersExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected ?int $accommodationId = null,
        protected ?int $transferId = null,
    ) {
    }

    public function collection(): Collection
    {
        $query = Transfer::with(['accommodation', 'booking', 'vehicleType']);

        if ($this->accommodationId) {
            $query->where('accommodation_id', $this->accommodationId);
        }

        if ($this->transferId) {
            $query->where('id', $this->transferId);
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'Event Name',
            'Client Name',
            'Client Phone',
            'Client Email',
            'Transfer Type',
            'Trip Type',
            'Transfer Date',
            'Pickup Time',
            'Pickup Location',
            'Drop-off Location',
            'Flight Number',
            'Flight Time',
            'Vehicle Type',
            'Passengers',
            'Additional Passengers',
            'Price (MAD)',
            'Return Date',
            'Return Time',
            'Status',
            'Payment Method',
            'Booking Reference',
            'Driver Name',
            'Driver Phone',
            'Created At',
        ];
    }

    public function map($transfer): array
    {
        return [
            $transfer->accommodation?->name,
            $transfer->client_name,
            $transfer->client_phone,
            $transfer->client_email,
            $transfer->transfer_type_label,
            $transfer->trip_type_label,
            optional($transfer->transfer_date)->format('Y-m-d'),
            $transfer->pickup_time,
            $transfer->pickup_location,
            $transfer->dropoff_location,
            $transfer->flight_number,
            $transfer->flight_time ? $transfer->flight_time->format('H:i') : null,
            $transfer->vehicle_type_label,
            $transfer->passengers,
            is_array($transfer->additional_passengers) ? implode(', ', array_filter($transfer->additional_passengers)) : '',
            $transfer->price,
            optional($transfer->return_date)->format('Y-m-d'),
            $transfer->return_time,
            $transfer->status_label,
            $transfer->payment_method_label,
            $transfer->booking?->booking_reference,
            $transfer->driver_name,
            $transfer->driver_phone,
            optional($transfer->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}
