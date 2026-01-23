@extends('layouts.admin')

@section('content')
<div class="space-y-6">
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
      <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">Flight Details</h1>
      <p class="text-gray-600 mt-1">{{ $accommodation->name }}</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('admin.flights.index', $accommodation) }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-all">
        Back to Flights
      </a>
      <a href="{{ route('admin.flights.edit', [$accommodation, $flight]) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-all">
        Edit
      </a>
    </div>
  </div>

  <x-shadcn.card class="shadow-lg">
    <x-shadcn.card-content class="p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <h3 class="text-lg font-semibold mb-4">Flight Information</h3>
          <div class="space-y-2">
            <div><span class="font-medium">Flight Reference:</span> {{ $flight->reference }}</div>
            @if($flight->ticket_reference)
              <div><span class="font-medium">Ticket Reference (Airline):</span> {{ $flight->ticket_reference }}</div>
            @endif
            <div><span class="font-medium">Client Name:</span> {{ $flight->full_name }}</div>
            <div><span class="font-medium">Flight Class:</span> {{ $flight->flight_class_label }}</div>
            @if($flight->eticket)
              <div><span class="font-medium">eTicket Number:</span> {{ $flight->eticket }}</div>
            @endif
          </div>
        </div>

        <div>
          <h3 class="text-lg font-semibold mb-4">Departure</h3>
          <div class="space-y-2">
            <div><span class="font-medium">Date:</span> {{ $flight->departure_date ? \Carbon\Carbon::parse($flight->departure_date)->format('Y-m-d') : '—' }}</div>
            <div><span class="font-medium">Time:</span> {{ $flight->departure_time ? \Carbon\Carbon::parse($flight->departure_time)->format('H:i') : '—' }}</div>
            <div><span class="font-medium">Flight Number:</span> {{ $flight->departure_flight_number }}</div>
            <div><span class="font-medium">Departure Airport:</span> {{ $flight->departure_airport ?? '—' }}</div>
            <div><span class="font-medium">Arrival:</span> {{ $flight->arrival_date ? \Carbon\Carbon::parse($flight->arrival_date)->format('Y-m-d') : '—' }} at {{ $flight->arrival_time ? \Carbon\Carbon::parse($flight->arrival_time)->format('H:i') : '—' }}</div>
            <div><span class="font-medium">Arrival Airport:</span> {{ $flight->arrival_airport ?? '—' }}</div>
          </div>
        </div>

        @if($flight->return_date)
        <div>
          <h3 class="text-lg font-semibold mb-4">Return</h3>
          <div class="space-y-2">
            <div><span class="font-medium">Date:</span> {{ \Carbon\Carbon::parse($flight->return_date)->format('Y-m-d') }}</div>
            <div><span class="font-medium">Time:</span> {{ $flight->return_departure_time ? \Carbon\Carbon::parse($flight->return_departure_time)->format('H:i') : '—' }}</div>
            <div><span class="font-medium">Flight Number:</span> {{ $flight->return_flight_number }}</div>
            <div><span class="font-medium">Departure Airport:</span> {{ $flight->return_departure_airport ?? '—' }}</div>
            <div><span class="font-medium">Arrival:</span> {{ $flight->return_arrival_date ? \Carbon\Carbon::parse($flight->return_arrival_date)->format('Y-m-d') : '—' }} at {{ $flight->return_arrival_time ? \Carbon\Carbon::parse($flight->return_arrival_time)->format('H:i') : '—' }}</div>
            <div><span class="font-medium">Arrival Airport:</span> {{ $flight->return_arrival_airport ?? '—' }}</div>
          </div>
        </div>
        @endif

        <div>
          <h3 class="text-lg font-semibold mb-4">Payment Information</h3>
          <div class="space-y-2">
            <div>
              <span class="font-medium">Status:</span>
              <x-shadcn.badge variant="{{ $flight->status === 'paid' ? 'default' : 'secondary' }}" class="ml-2">
                {{ $flight->status === 'paid' ? 'Paid' : 'Pending' }}
              </x-shadcn.badge>
            </div>
            @if($flight->payment_method)
              <div>
                <span class="font-medium">Payment Method:</span>
                @php
                  $paymentTypeLabels = [
                    'wallet' => 'Portefeuille',
                    'bank' => 'Virement Bancaire',
                    'both' => 'Mixte (Portefeuille + Virement)'
                  ];
                @endphp
                <span class="ml-2">{{ $paymentTypeLabels[$flight->payment_method] ?? strtoupper($flight->payment_method) }}</span>
              </div>
            @endif
          </div>
        </div>

        <div>
          <h3 class="text-lg font-semibold mb-4">Beneficiary</h3>
          <div class="space-y-2">
            <div><span class="font-medium">Type:</span> {{ ucfirst($flight->beneficiary_type) }}</div>
            @if($flight->beneficiary_type === 'client')
              <div><span class="font-medium">Email:</span> {{ $flight->client_email }}</div>
              @if($flight->credentials_pdf_path)
                <div>
                  <a href="{{ route('admin.flights.downloadCredentials', [$accommodation, $flight]) }}" 
                     class="text-blue-600 hover:underline">
                    Download Credentials PDF
                  </a>
                </div>
              @endif
            @else
              <div><span class="font-medium">Organizer:</span> {{ $flight->organizer->name ?? 'N/A' }}</div>
            @endif
          </div>
        </div>

        @if($flight->eticket_path || $flight->eticket || $flight->ticket_reference)
        <div>
          <h3 class="text-lg font-semibold mb-4">eTicket Information</h3>
          @if($flight->eticket_path)
            <div class="mb-2">
              <span class="font-medium">eTicket File:</span>
              <a href="{{ $flight->eticket_url }}" target="_blank" class="text-blue-600 hover:underline ml-2">
                View eTicket File
              </a>
            </div>
          @endif
          @if($flight->eticket)
            <div class="mb-2">
              <span class="font-medium">eTicket Number:</span> {{ $flight->eticket }}
            </div>
          @endif
          @if($flight->ticket_reference)
            <div class="mb-2">
              <span class="font-medium">Ticket Reference (Airline):</span> {{ $flight->ticket_reference }}
            </div>
          @endif
        </div>
        @endif
      </div>

      @if($flight->bookings->count() > 0)
      <div class="mt-6">
        <h3 class="text-lg font-semibold mb-4">Related Bookings</h3>
        <div class="space-y-2">
          @foreach($flight->bookings as $booking)
            <div class="p-3 bg-gray-50 rounded">
              <a href="{{ route('admin.bookings.index') }}?search={{ $booking->booking_reference }}" 
                 class="text-blue-600 hover:underline">
                Booking: {{ $booking->booking_reference }}
              </a>
            </div>
          @endforeach
        </div>
      </div>
      @endif
    </x-shadcn.card-content>
  </x-shadcn.card>
</div>
@endsection

