@extends('layouts.admin')

@section('content')
<div class="space-y-6">
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">{{ __('Bookings') }}</h1>
  </div>

  {{-- Filters --}}
  <x-shadcn.card class="shadow-lg">
    <x-shadcn.card-content class="p-6">
      <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex flex-col sm:flex-row gap-3 sm:gap-4">
        <div class="flex-1 min-w-0">
          <input type="text" name="search" placeholder="Search by reference, name, email, phone..." value="{{ request('search') }}" 
                 class="w-full bg-white text-gray-900 border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm sm:text-base" style="focus:border-color: #00adf1; focus:ring-color: #00adf1;">
        </div>
        <div class="w-full sm:w-auto">
          <select name="status" class="w-full sm:w-auto bg-white text-gray-900 border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm sm:text-base" style="focus:border-color: #00adf1; focus:ring-color: #00adf1;">
            <option value="">All Statuses</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Remboursé</option>
          </select>
        </div>
        <div class="w-full sm:w-auto">
          <select name="accommodation_id" class="w-full sm:w-auto bg-white text-gray-900 border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm sm:text-base" style="focus:border-color: #00adf1; focus:ring-color: #00adf1;">
            <option value="">All Accommodations</option>
            @foreach($accommodations as $accommodation)
              <option value="{{ $accommodation->id }}" {{ request('accommodation_id') == $accommodation->id ? 'selected' : '' }}>{{ $accommodation->name }}</option>
            @endforeach
          </select>
        </div>
        <button type="submit" class="text-white px-4 sm:px-6 py-2 rounded-md font-semibold transition-all text-sm sm:text-base whitespace-nowrap" style="background-color: #00adf1;" onmouseover="this.style.backgroundColor='#0099d8'" onmouseout="this.style.backgroundColor='#00adf1'">Filter</button>
        <a href="{{ route('admin.bookings.index') }}" class="px-4 sm:px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-all text-sm sm:text-base text-center whitespace-nowrap">Clear</a>
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
            <x-shadcn.table-head>Payment</x-shadcn.table-head>
            <x-shadcn.table-head>Documents</x-shadcn.table-head>
            <x-shadcn.table-head>Status</x-shadcn.table-head>
            <x-shadcn.table-head>Invoice</x-shadcn.table-head>
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
              <x-shadcn.table-cell class="font-medium break-words">{{ $booking->booking_reference ?? 'N/A' }}</x-shadcn.table-cell>
              <x-shadcn.table-cell class="break-words">
                <div class="break-words">{{ $booking->full_name ?? $booking->guest_name ?? 'N/A' }}</div>
                <div class="text-xs text-muted-foreground break-all">{{ $booking->email ?? $booking->guest_email ?? '-' }}</div>
              </x-shadcn.table-cell>
              <x-shadcn.table-cell class="break-words">{{ $booking->accommodation->name ?? 'N/A' }}</x-shadcn.table-cell>
              <x-shadcn.table-cell class="break-words">{{ $booking->hotel->name ?? 'N/A' }}</x-shadcn.table-cell>
              <x-shadcn.table-cell class="break-words">{{ $booking->package->nom_package ?? 'N/A' }}</x-shadcn.table-cell>
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
                @if($booking->payment_type)
                  <div class="flex flex-col gap-1">
                    @php
                      $paymentTypeLabels = [
                        'wallet' => 'Portefeuille',
                        'bank' => 'Virement',
                        'both' => 'Mixte'
                      ];
                      $paymentTypeColors = [
                        'wallet' => 'bg-green-100 text-green-700',
                        'bank' => 'bg-blue-100 text-blue-700',
                        'both' => 'bg-purple-100 text-purple-700'
                      ];
                    @endphp
                    <span class="text-xs px-2 py-1 rounded {{ $paymentTypeColors[$booking->payment_type] ?? 'bg-gray-100 text-gray-700' }}">
                      {{ $paymentTypeLabels[$booking->payment_type] ?? strtoupper($booking->payment_type) }}
                    </span>
                    @if($booking->wallet_amount > 0 || $booking->bank_amount > 0)
                      <div class="text-xs text-gray-600">
                        @if($booking->wallet_amount > 0)
                          <div>Portefeuille: {{ number_format($booking->wallet_amount, 2, '.', '') }} MAD</div>
                        @endif
                        @if($booking->bank_amount > 0)
                          <div>Virement: {{ number_format($booking->bank_amount, 2, '.', '') }} MAD</div>
                        @endif
                      </div>
                    @endif
                  </div>
                @else
                  <span class="text-xs text-muted-foreground">N/A</span>
                @endif
              </x-shadcn.table-cell>
              <x-shadcn.table-cell>
                <div class="flex flex-col gap-1">
                  @if($booking->payment_document_path)
                    <a href="{{ route('admin.bookings.downloadPaymentDocument', $booking) }}" 
                       target="_blank"
                       class="flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 transition-colors"
                       title="Télécharger l'ordre de paiement">
                      <i data-lucide="file-text" class="w-3 h-3"></i>
                      <span>Ordre de paiement</span>
                    </a>
                  @else
                    <span class="text-xs text-gray-400">Pas d'ordre de paiement</span>
                  @endif
                  @if($booking->flight_ticket_path)
                    <a href="{{ route('admin.bookings.downloadFlightTicket', $booking) }}" 
                       target="_blank"
                       class="flex items-center gap-1 text-xs text-green-600 hover:text-green-800 transition-colors"
                       title="Télécharger le billet d'avion">
                      <i data-lucide="plane" class="w-3 h-3"></i>
                      <span>Billet d'avion</span>
                    </a>
                  @else
                    <span class="text-xs text-gray-400">Pas de billet</span>
                  @endif
                </div>
              </x-shadcn.table-cell>
              <x-shadcn.table-cell>
                <x-shadcn.badge variant="{{ $booking->status === 'confirmed' || $booking->status === 'paid' ? 'default' : ($booking->status === 'pending' ? 'secondary' : ($booking->status === 'refunded' ? 'outline' : 'destructive')) }}">
                  {{ $booking->status === 'refunded' ? 'Remboursé' : ($booking->status === 'paid' ? 'Payé' : ucfirst($booking->status)) }}
                </x-shadcn.badge>
                @if($booking->status === 'refunded' && $booking->refund_amount)
                  <div class="text-xs text-gray-600 mt-1">
                    Montant: {{ number_format($booking->refund_amount, 2, '.', '') }} MAD
                  </div>
                @endif
              </x-shadcn.table-cell>
              <x-shadcn.table-cell>
                @if($booking->invoice)
                  <div class="flex flex-col gap-1">
                    <a href="{{ route('admin.invoices.show', $booking->invoice) }}" 
                       class="text-logo-link hover:underline text-sm font-medium">
                      {{ $booking->invoice->invoice_number }}
                    </a>
                    <x-shadcn.badge variant="{{ $booking->invoice->status === 'paid' ? 'default' : ($booking->invoice->status === 'sent' ? 'secondary' : 'outline') }}" class="text-xs">
                      {{ strtoupper($booking->invoice->status) }}
                    </x-shadcn.badge>
                  </div>
                @else
                  <span class="text-xs text-muted-foreground">No invoice</span>
                @endif
              </x-shadcn.table-cell>
              <x-shadcn.table-cell>{{ $booking->created_at->format('Y-m-d') }}</x-shadcn.table-cell>
              <x-shadcn.table-cell>
                <div class="flex items-center gap-2">
                  <form method="POST" action="{{ route('admin.bookings.updateStatus', $booking) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <select name="status" onchange="this.form.submit()" class="text-xs bg-white text-gray-900 border border-gray-300 rounded px-2 py-1" {{ $booking->status === 'refunded' ? 'disabled' : '' }}>
                      <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>Pending</option>
                      <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                      <option value="paid" {{ $booking->status === 'paid' ? 'selected' : '' }}>Paid</option>
                      <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                      <option value="refunded" {{ $booking->status === 'refunded' ? 'selected' : '' }}>Remboursé</option>
                    </select>
                  </form>
                  @if($booking->status !== 'refunded')
                    <button 
                      type="button"
                      x-data
                      @click="$dispatch('open-modal', 'confirm-booking-refund-{{ $booking->id }}')"
                      class="text-orange-600 hover:text-orange-800 transition-colors cursor-pointer"
                      title="Rembourser">
                      <i data-lucide="dollar-sign" class="w-4 h-4"></i>
                    </button>
                  @endif
                  <button 
                    type="button"
                    x-data
                    @click="$dispatch('open-modal', 'confirm-booking-deletion-{{ $booking->id }}')"
                    class="text-red-600 hover:text-red-800 transition-colors cursor-pointer"
                    title="Delete booking">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                  </button>
                </div>
              </x-shadcn.table-cell>
            </x-shadcn.table-row>
            {{-- Expanded Details Row --}}
            <x-shadcn.table-row id="details-{{ $booking->id }}" class="hidden">
              <x-shadcn.table-cell colspan="12" class="bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-xs sm:text-sm p-4">
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
                    @if($booking->flight)
                      {{-- Flight booking (from flights table) --}}
                      <div><span class="font-medium">Flight Reference:</span> {{ $booking->flight->reference }}</div>
                      <div><span class="font-medium">Flight Class:</span> {{ $booking->flight->flight_class_label }}</div>
                      <div><span class="font-medium">Departure:</span> {{ $booking->flight->departure_date ? \Carbon\Carbon::parse($booking->flight->departure_date)->format('Y-m-d') : '—' }} at {{ $booking->flight->departure_time ? \Carbon\Carbon::parse($booking->flight->departure_time)->format('H:i') : '—' }} - {{ $booking->flight->departure_flight_number }}</div>
                      <div><span class="font-medium">Airports:</span> {{ $booking->flight->departure_airport ?? '—' }} → {{ $booking->flight->arrival_airport ?? '—' }}</div>
                      <div><span class="font-medium">Arrival:</span> {{ $booking->flight->arrival_date ? \Carbon\Carbon::parse($booking->flight->arrival_date)->format('Y-m-d') : '—' }} at {{ $booking->flight->arrival_time ? \Carbon\Carbon::parse($booking->flight->arrival_time)->format('H:i') : '—' }}</div>
                      @if($booking->flight->return_date)
                        <div><span class="font-medium">Return:</span> {{ \Carbon\Carbon::parse($booking->flight->return_date)->format('Y-m-d') }} at {{ $booking->flight->return_departure_time ? \Carbon\Carbon::parse($booking->flight->return_departure_time)->format('H:i') : '—' }} - {{ $booking->flight->return_flight_number }}</div>
                        <div><span class="font-medium">Return Airports:</span> {{ $booking->flight->return_departure_airport ?? '—' }} → {{ $booking->flight->return_arrival_airport ?? '—' }}</div>
                      @endif
                    @else
                      {{-- Regular booking flight info --}}
                    <div><span class="font-medium">Flight Number:</span> {{ $booking->flight_number ?? 'N/A' }}</div>
                    <div><span class="font-medium">Flight Date:</span> {{ $booking->flight_date ? $booking->flight_date->format('Y-m-d') : 'N/A' }}</div>
                    <div><span class="font-medium">Flight Time:</span> {{ $booking->flight_time ? $booking->flight_time->format('H:i') : 'N/A' }}</div>
                    <div><span class="font-medium">Airport:</span> {{ $booking->airport ?? 'N/A' }}</div>
                    @endif
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
                    <div><span class="font-medium">Accommodation:</span> {{ $booking->accommodation->name ?? 'N/A' }}</div>
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

                  {{-- Documents --}}
                  <div class="space-y-2">
                    <h4 class="font-semibold mb-2">Documents</h4>
                    @if($booking->payment_document_path)
                      <div class="flex items-center gap-2">
                        <span class="font-medium">Ordre de paiement:</span>
                        <a href="{{ route('admin.bookings.downloadPaymentDocument', $booking) }}" 
                           target="_blank"
                           class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1">
                          <i data-lucide="download" class="w-4 h-4"></i>
                          Télécharger
                        </a>
                      </div>
                    @else
                      <div class="text-sm text-gray-500">Pas d'ordre de paiement</div>
                    @endif
                    @if($booking->flight_ticket_path)
                      <div class="flex items-center gap-2">
                        <span class="font-medium">Billet d'avion:</span>
                        <a href="{{ route('admin.bookings.downloadFlightTicket', $booking) }}" 
                           target="_blank"
                           class="text-green-600 hover:text-green-800 text-sm flex items-center gap-1">
                          <i data-lucide="download" class="w-4 h-4"></i>
                          Télécharger
                        </a>
                      </div>
                    @else
                      <div class="text-sm text-gray-500">Pas de billet d'avion</div>
                    @endif
                  </div>

                  {{-- Payment Information --}}
                  <div class="space-y-2">
                    <h4 class="font-semibold mb-2">Payment Information</h4>
                    @if($booking->payment_type)
                      @php
                        $paymentTypeLabels = [
                          'wallet' => 'Portefeuille',
                          'bank' => 'Virement Bancaire',
                          'both' => 'Mixte (Portefeuille + Virement)'
                        ];
                      @endphp
                      <div><span class="font-medium">Payment Method:</span> 
                        <span class="font-semibold">{{ $paymentTypeLabels[$booking->payment_type] ?? strtoupper($booking->payment_type) }}</span>
                      </div>
                      <div><span class="font-medium">Total Amount:</span> 
                        <span class="font-semibold text-green-600">{{ number_format((float) $booking->price, 2, '.', '') }} MAD</span>
                      </div>
                      @if($booking->wallet_amount > 0)
                        <div><span class="font-medium">Wallet Amount:</span> 
                          <span class="text-green-600">{{ number_format((float) $booking->wallet_amount, 2, '.', '') }} MAD</span>
                        </div>
                      @endif
                      @if($booking->bank_amount > 0)
                        <div><span class="font-medium">Bank Amount:</span> 
                          <span class="text-blue-600">{{ number_format((float) $booking->bank_amount, 2, '.', '') }} MAD</span>
                        </div>
                      @endif
                    @else
                      <div class="text-muted-foreground text-sm">No payment information available.</div>
                    @endif
                  </div>

                  {{-- Invoice Information --}}
                  <div class="space-y-2">
                    <h4 class="font-semibold mb-2">Invoice Information</h4>
                    @if($booking->invoice)
                      <div><span class="font-medium">Invoice Number:</span> 
                        <a href="{{ route('admin.invoices.show', $booking->invoice) }}" class="text-logo-link hover:underline">
                          {{ $booking->invoice->invoice_number }}
                        </a>
                      </div>
                      <div><span class="font-medium">Status:</span> 
                        <x-shadcn.badge variant="{{ $booking->invoice->status === 'paid' ? 'default' : ($booking->invoice->status === 'sent' ? 'secondary' : 'outline') }}" class="text-xs">
                          {{ strtoupper($booking->invoice->status) }}
                        </x-shadcn.badge>
                      </div>
                      <div><span class="font-medium">Total Amount:</span> 
                        <span class="font-semibold text-green-600">{{ number_format((float) $booking->invoice->total_amount, 2, '.', '') }} MAD</span>
                      </div>
                      <div class="flex gap-2 mt-2">
                        <a href="{{ route('admin.invoices.show', $booking->invoice) }}" 
                           class="text-xs px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors">
                          View Invoice
                        </a>
                        <a href="{{ route('admin.invoices.pdf', $booking->invoice) }}" target="_blank"
                           class="text-xs px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">
                          View PDF
                        </a>
                        @if($booking->invoice->status !== 'sent' && $booking->invoice->status !== 'paid')
                          <form method="POST" action="{{ route('admin.invoices.send', $booking->invoice) }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="text-xs px-3 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 transition-colors"
                                    onclick="return confirm('Send invoice to {{ $booking->email ?? $booking->guest_email }}?');">
                              Send Invoice
                            </button>
                          </form>
                        @endif
                      </div>
                    @else
                      <div class="text-muted-foreground text-sm">No invoice generated yet.</div>
                    @endif
                  </div>

                  {{-- Refund Information --}}
                  @if($booking->status === 'refunded')
                    <div class="space-y-2 md:col-span-2 lg:col-span-3">
                      <h4 class="font-semibold mb-2 text-orange-600">Informations de Remboursement</h4>
                      <div class="bg-orange-50 border border-orange-200 p-3 rounded">
                        <div><span class="font-medium">Montant remboursé:</span> 
                          <span class="font-semibold text-orange-600">{{ number_format($booking->refund_amount ?? 0, 2, '.', '') }} MAD</span>
                        </div>
                        <div><span class="font-medium">Date de remboursement:</span> 
                          {{ $booking->refunded_at ? $booking->refunded_at->format('d/m/Y H:i') : 'N/A' }}
                        </div>
                        @if($booking->refund_notes)
                          <div class="mt-2"><span class="font-medium">Notes:</span> 
                            <div class="text-sm text-gray-700 mt-1">{{ $booking->refund_notes }}</div>
                          </div>
                        @endif
                      </div>
                    </div>
                  @endif

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
              <x-shadcn.table-cell colspan="12" class="text-center text-muted-foreground">No bookings found.</x-shadcn.table-cell>
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
          @click.stop="$dispatch('close-modal', 'confirm-booking-deletion-{{ $booking->id }}')"
          class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
          {{ __('Cancel') }}
        </button>

        <x-danger-button type="submit" @click.stop>
          {{ __('Delete Booking') }}
        </x-danger-button>
      </div>
    </form>
  </x-modal>
@endforeach

{{-- Refund Modals --}}
@foreach($bookings as $booking)
  @if($booking->status !== 'refunded')
    @php
      $totalPrice = $booking->price ?? ($booking->package->prix_ttc ?? 0);
    @endphp
    <x-modal name="confirm-booking-refund-{{ $booking->id }}" :show="false" focusable>
      <form method="post" action="{{ route('admin.bookings.refund', $booking) }}" class="p-6">
        @csrf

        <h2 class="text-lg font-medium text-gray-900 mb-4">
          {{ __('Remboursement') }}
        </h2>

        <p class="text-sm text-gray-600 mb-4">
          {{ __('Traiter le remboursement pour cette réservation:') }}<br>
          <strong>Référence:</strong> {{ $booking->booking_reference }}<br>
          <strong>Client:</strong> {{ $booking->full_name ?? $booking->guest_name ?? 'N/A' }}<br>
          <strong>Montant total:</strong> {{ number_format($totalPrice, 2, '.', '') }} MAD
        </p>

        <div class="space-y-4 mb-6">
          <div>
            <label for="amount-{{ $booking->id }}" class="block text-sm font-medium text-gray-700 mb-1">
              Montant remboursé <span class="text-red-500">*</span>
            </label>
            <input
              type="number"
              id="amount-{{ $booking->id }}"
              name="amount"
              step="0.01"
              min="0"
              max="{{ $totalPrice }}"
              value="{{ $totalPrice }}"
              required
              class="w-full bg-white text-gray-900 border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:border-blue-500 focus:ring-blue-500">
            <p class="text-xs text-gray-500 mt-1">Maximum: {{ number_format($totalPrice, 2, '.', '') }} MAD</p>
          </div>

          <div>
            <label for="notes-{{ $booking->id }}" class="block text-sm font-medium text-gray-700 mb-1">
              Notes (optionnel)
            </label>
            <textarea
              id="notes-{{ $booking->id }}"
              name="notes"
              rows="3"
              maxlength="1000"
              class="w-full bg-white text-gray-900 border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
              placeholder="Raison du remboursement, détails, etc."></textarea>
          </div>
        </div>

        <p class="text-sm text-yellow-600 mb-6">
          {{ __('Attention: Cette action changera le statut de la réservation en "Remboursé" et rendra la chambre disponible.') }}
        </p>

        <div class="flex justify-end gap-3">
          <button
            type="button"
            @click.stop="$dispatch('close-modal', 'confirm-booking-refund-{{ $booking->id }}')"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
            {{ __('Annuler') }}
          </button>

          <button
            type="submit"
            @click.stop
            class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 transition-colors font-semibold">
            {{ __('Confirmer Remboursement') }}
          </button>
        </div>
      </form>
    </x-modal>
  @endif
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
