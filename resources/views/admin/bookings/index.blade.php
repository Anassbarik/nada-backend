@extends('layouts.admin')

@section('content')
<div class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-4xl font-bold">{{ __('Bookings') }}</h1>
  </div>

  {{-- Filters --}}
  <x-shadcn.card class="shadow-lg">
    <x-shadcn.card-content class="p-6">
      <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex gap-4 flex-wrap">
        <div class="flex-1 min-w-[200px]">
          <input type="text" name="search" placeholder="Search by reference, name, email, phone..." value="{{ request('search') }}" 
                 class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2" style="focus:border-color: #00adf1; focus:ring-color: #00adf1;">
        </div>
        <div>
          <select name="status" class="border border-gray-300 rounded-md shadow-sm px-3 py-2" style="focus:border-color: #00adf1; focus:ring-color: #00adf1;">
            <option value="">All Statuses</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
          </select>
        </div>
        <div>
          <select name="event_id" class="border border-gray-300 rounded-md shadow-sm px-3 py-2" style="focus:border-color: #00adf1; focus:ring-color: #00adf1;">
            <option value="">All Events</option>
            @foreach($events as $event)
              <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>{{ $event->name }}</option>
            @endforeach
          </select>
        </div>
        <button type="submit" class="text-white px-6 py-2 rounded-md font-semibold transition-all" style="background-color: #00adf1;" onmouseover="this.style.backgroundColor='#0099d8'" onmouseout="this.style.backgroundColor='#00adf1'">Filter</button>
        <a href="{{ route('admin.bookings.index') }}" class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-all">Clear</a>
      </form>
    </x-shadcn.card-content>
  </x-shadcn.card>

  {{-- Bookings Table --}}
  <x-shadcn.card class="shadow-lg">
    <x-shadcn.card-content class="p-0">
      <x-shadcn.table responsive>
        <x-shadcn.table-header>
          <x-shadcn.table-row>
            <x-shadcn.table-head></x-shadcn.table-head>
            <x-shadcn.table-head>Ref</x-shadcn.table-head>
            <x-shadcn.table-head>Guest</x-shadcn.table-head>
            <x-shadcn.table-head>Event</x-shadcn.table-head>
            <x-shadcn.table-head>Hotel</x-shadcn.table-head>
            <x-shadcn.table-head>Package</x-shadcn.table-head>
            <x-shadcn.table-head>Price (HT/TTC)</x-shadcn.table-head>
            <x-shadcn.table-head>Status</x-shadcn.table-head>
            <x-shadcn.table-head>Date</x-shadcn.table-head>
            <x-shadcn.table-head>Actions</x-shadcn.table-head>
          </x-shadcn.table-row>
        </x-shadcn.table-header>
        <x-shadcn.table-body>
          @forelse($bookings as $booking)
            <x-shadcn.table-row hover>
              <x-shadcn.table-cell>
                <button onclick="toggleDetails({{ $booking->id }})" class="text-logo-link hover:underline">
                  <i data-lucide="chevron-down" id="icon-{{ $booking->id }}" class="w-5 h-5 transition-transform"></i>
                </button>
              </x-shadcn.table-cell>
              <x-shadcn.table-cell class="font-medium">{{ $booking->booking_reference ?? 'N/A' }}</x-shadcn.table-cell>
              <x-shadcn.table-cell>
                <div>{{ $booking->full_name ?? $booking->guest_name ?? 'N/A' }}</div>
                <div class="text-xs text-muted-foreground">{{ $booking->email ?? $booking->guest_email ?? '-' }}</div>
              </x-shadcn.table-cell>
              <x-shadcn.table-cell>{{ $booking->event->name ?? 'N/A' }}</x-shadcn.table-cell>
              <x-shadcn.table-cell>{{ $booking->hotel->name ?? 'N/A' }}</x-shadcn.table-cell>
              <x-shadcn.table-cell>{{ $booking->package->nom_package ?? 'N/A' }}</x-shadcn.table-cell>
              <x-shadcn.table-cell>
                @php
                  $bookingPrice = $booking->price ?? ($booking->package->prix_ttc ?? null);
                  $packageHT = $booking->package->prix_ht ?? null;
                  $packageTTC = $booking->package->prix_ttc ?? null;
                  
                  // Format price without trailing .00
                  $formatPrice = function($price) {
                    if ($price === null) return 'N/A';
                    $formatted = rtrim(rtrim(number_format($price, 2, '.', ''), '0'), '.');
                    return $formatted . ' MAD';
                  };
                @endphp
                @if($bookingPrice)
                  <div class="font-semibold text-green-600">{{ $formatPrice($bookingPrice) }}</div>
                @endif
                @if($packageHT && $packageTTC)
                  <div class="text-xs text-gray-600">
                    <span>{{ $formatPrice($packageHT) }} HT</span> / 
                    <span>{{ $formatPrice($packageTTC) }} TTC</span>
                  </div>
                @else
                  <div class="text-xs text-gray-500">N/A</div>
                @endif
              </x-shadcn.table-cell>
              <x-shadcn.table-cell>
                <x-shadcn.badge variant="{{ $booking->status === 'confirmed' ? 'default' : ($booking->status === 'pending' ? 'secondary' : 'destructive') }}">
                  {{ ucfirst($booking->status) }}
                </x-shadcn.badge>
              </x-shadcn.table-cell>
              <x-shadcn.table-cell>{{ $booking->created_at->format('Y-m-d') }}</x-shadcn.table-cell>
              <x-shadcn.table-cell>
                <div class="flex items-center gap-2">
                  <form method="POST" action="{{ route('admin.bookings.updateStatus', $booking) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <select name="status" onchange="this.form.submit()" class="text-xs border border-gray-300 rounded px-2 py-1">
                      <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>Pending</option>
                      <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                      <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                  </form>
                  <button 
                    type="button"
                    x-data=""
                    x-on:click="$dispatch('open-modal', 'confirm-booking-deletion-{{ $booking->id }}')"
                    class="text-red-600 hover:text-red-800 transition-colors"
                    title="Delete booking">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                  </button>
                </div>
              </x-shadcn.table-cell>
            </x-shadcn.table-row>
            {{-- Expanded Details Row --}}
            <x-shadcn.table-row id="details-{{ $booking->id }}" class="hidden">
              <x-shadcn.table-cell colspan="10" class="bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm p-4">
                  {{-- Client Information --}}
                  <div class="space-y-2">
                    <h4 class="font-semibold mb-2">Client Information</h4>
                    <div><span class="font-medium">Full Name:</span> {{ $booking->full_name ?? $booking->guest_name ?? 'N/A' }}</div>
                    <div><span class="font-medium">Company:</span> {{ $booking->company ?? 'N/A' }}</div>
                    <div><span class="font-medium">Email:</span> {{ $booking->email ?? $booking->guest_email ?? 'N/A' }}</div>
                    <div><span class="font-medium">Phone:</span> {{ $booking->phone ?? $booking->guest_phone ?? 'N/A' }}</div>
                  </div>

                  {{-- Flight Information --}}
                  <div class="space-y-2">
                    <h4 class="font-semibold mb-2">Flight Information</h4>
                    <div><span class="font-medium">Flight Number:</span> {{ $booking->flight_number ?? 'N/A' }}</div>
                    <div><span class="font-medium">Flight Date:</span> {{ $booking->flight_date ? $booking->flight_date->format('Y-m-d') : 'N/A' }}</div>
                    <div><span class="font-medium">Flight Time:</span> {{ $booking->flight_time ? $booking->flight_time->format('H:i') : 'N/A' }}</div>
                    <div><span class="font-medium">Airport:</span> {{ $booking->airport ?? 'N/A' }}</div>
                  </div>

                  {{-- Booking Details --}}
                  <div class="space-y-2">
                    <h4 class="font-semibold mb-2">Booking Details</h4>
                    <div><span class="font-medium">Booking Reference:</span> {{ $booking->booking_reference ?? 'N/A' }}</div>
                    <div><span class="font-medium">Check-in Date:</span> {{ $booking->checkin_date ? $booking->checkin_date->format('Y-m-d') : 'N/A' }}</div>
                    <div><span class="font-medium">Check-out Date:</span> {{ $booking->checkout_date ? $booking->checkout_date->format('Y-m-d') : 'N/A' }}</div>
                    <div><span class="font-medium">Guests Count:</span> {{ $booking->guests_count ?? 'N/A' }}</div>
                    @php
                      $bookingPrice = $booking->price ?? ($booking->package->prix_ttc ?? null);
                      $packageHT = $booking->package->prix_ht ?? null;
                      $packageTTC = $booking->package->prix_ttc ?? null;
                      
                      // Format price without trailing .00
                      $formatPrice = function($price) {
                        if ($price === null) return 'N/A';
                        $formatted = rtrim(rtrim(number_format($price, 2, '.', ''), '0'), '.');
                        return $formatted . ' MAD';
                      };
                    @endphp
                    <div>
                      <span class="font-medium">Booking Price:</span> 
                      <span class="font-semibold text-green-600">{{ $bookingPrice ? $formatPrice($bookingPrice) : 'N/A' }}</span>
                    </div>
                    @if($packageHT && $packageTTC)
                      <div>
                        <span class="font-medium">Package Prices:</span> 
                        <span>{{ $formatPrice($packageHT) }} HT</span> / 
                        <span>{{ $formatPrice($packageTTC) }} TTC</span>
                      </div>
                    @endif
                  </div>

                  {{-- Event & Hotel --}}
                  <div class="space-y-2">
                    <h4 class="font-semibold mb-2">Event & Hotel</h4>
                    <div><span class="font-medium">Event:</span> {{ $booking->event->name ?? 'N/A' }}</div>
                    <div><span class="font-medium">Hotel:</span> {{ $booking->hotel->name ?? 'N/A' }}</div>
                    <div><span class="font-medium">Package:</span> {{ $booking->package->nom_package ?? 'N/A' }}</div>
                    <div><span class="font-medium">Package Type:</span> {{ $booking->package->type_chambre ?? 'N/A' }}</div>
                  </div>

                  {{-- Resident Names --}}
                  <div class="space-y-2">
                    <h4 class="font-semibold mb-2">Resident Names</h4>
                    <div><span class="font-medium">Resident 1:</span> {{ $booking->resident_name_1 ?? 'N/A' }}</div>
                    <div><span class="font-medium">Resident 2:</span> {{ $booking->resident_name_2 ?? 'N/A' }}</div>
                  </div>

                  {{-- Special Instructions --}}
                  <div class="space-y-2 md:col-span-2 lg:col-span-3">
                    <h4 class="font-semibold mb-2">Special Instructions / Requests</h4>
                    <div class="bg-gray-100 p-3 rounded">
                      {{ $booking->special_instructions ?? $booking->special_requests ?? 'None' }}
                    </div>
                  </div>
                </div>
              </x-shadcn.table-cell>
            </x-shadcn.table-row>
          @empty
            <x-shadcn.table-row>
              <x-shadcn.table-cell colspan="10" class="text-center text-muted-foreground">No bookings found.</x-shadcn.table-cell>
            </x-shadcn.table-row>
          @endforelse
        </x-shadcn.table-body>
      </x-shadcn.table>
    </x-shadcn.card-content>
  </x-shadcn.card>
  
  <div class="mt-4">
    {{ $bookings->links() }}
  </div>
</div>

{{-- Delete Confirmation Modals --}}
@foreach($bookings as $booking)
  <x-modal name="confirm-booking-deletion-{{ $booking->id }}" :show="false" focusable>
    <form method="post" action="{{ route('admin.bookings.destroy', $booking) }}" class="p-6">
      @csrf
      @method('delete')

      <h2 class="text-lg font-medium text-gray-900 mb-4">
        {{ __('Delete Booking') }}
      </h2>

      <p class="text-sm text-gray-600 mb-4">
        {{ __('Are you sure you want to delete this booking?') }}<br>
        <strong>Reference:</strong> {{ $booking->booking_reference }}<br>
        <strong>Guest:</strong> {{ $booking->full_name ?? $booking->guest_name ?? 'N/A' }}
      </p>

      <p class="text-sm text-yellow-600 mb-6">
        {{ __('Warning: This action cannot be undone. If this booking was not cancelled, the room will be made available again.') }}
      </p>

      <div class="flex justify-end gap-3">
        <button
          type="button"
          x-on:click="$dispatch('close')"
          class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
          {{ __('Cancel') }}
        </button>

        <x-danger-button type="submit">
          {{ __('Delete Booking') }}
        </x-danger-button>
      </div>
    </form>
  </x-modal>
@endforeach

@push('scripts')
<script>
  function toggleDetails(bookingId) {
    const detailsRow = document.getElementById('details-' + bookingId);
    const icon = document.getElementById('icon-' + bookingId);
    
    if (detailsRow.classList.contains('hidden')) {
      detailsRow.classList.remove('hidden');
      icon.style.transform = 'rotate(180deg)';
    } else {
      detailsRow.classList.add('hidden');
      icon.style.transform = 'rotate(0deg)';
    }
  }

  // Initialize Lucide icons after page load
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
  });
</script>
@endpush
@endsection
