@extends('layouts.admin')

@section('content')
  <div class="space-y-8">
    {{-- Maintenance Mode Toggle --}}
    <x-shadcn.card class="shadow-lg border-l-4" style="border-left-color: #f59e0b;">
      <x-shadcn.card-content class="p-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div class="flex-1 min-w-0">
            <h3 class="text-base sm:text-lg font-semibold mb-1 break-words">Maintenance Mode</h3>
            <p class="text-xs sm:text-sm text-muted-foreground break-words">Control maintenance status for the frontend
              application</p>
          </div>
          <div class="flex items-center gap-4">
            <div class="flex items-center gap-3">
              <label class="flex items-center gap-2 cursor-pointer">
                <span class="text-sm font-medium">Home:</span>
                <form method="POST" action="{{ route('admin.maintenance.toggle') }}" class="inline">
                  @csrf
                  <input type="hidden" name="type" value="home">
                  <input type="hidden" name="enabled" value="{{ Cache::get('maintenance.home', false) ? '0' : '1' }}">
                  <button type="submit" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors
                          {{ Cache::get('maintenance.home', false) ? 'bg-orange-600' : 'bg-gray-300' }}">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform
                            {{ Cache::get('maintenance.home', false) ? 'translate-x-6' : 'translate-x-1' }}">
                    </span>
                  </button>
                </form>
                <span class="text-xs text-muted-foreground">
                  {{ Cache::get('maintenance.home', false) ? 'ON' : 'OFF' }}
                </span>
              </label>
            </div>
            <div class="flex items-center gap-3">
              <label class="flex items-center gap-2 cursor-pointer">
                <span class="text-sm font-medium">Global:</span>
                <form method="POST" action="{{ route('admin.maintenance.toggle') }}" class="inline">
                  @csrf
                  <input type="hidden" name="type" value="global">
                  <input type="hidden" name="enabled" value="{{ Cache::get('maintenance.global', false) ? '0' : '1' }}">
                  <button type="submit" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors
                          {{ Cache::get('maintenance.global', false) ? 'bg-red-600' : 'bg-gray-300' }}">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform
                            {{ Cache::get('maintenance.global', false) ? 'translate-x-6' : 'translate-x-1' }}">
                    </span>
                  </button>
                </form>
                <span class="text-xs text-muted-foreground">
                  {{ Cache::get('maintenance.global', false) ? 'ON' : 'OFF' }}
                </span>
              </label>
            </div>
          </div>
        </div>
      </x-shadcn.card-content>
    </x-shadcn.card>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Revenue Card -->
      <x-shadcn.card
        class="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 group">
        <div
          class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-blue-500/10 rounded-full group-hover:scale-110 transition-transform duration-300">
        </div>
        <x-shadcn.card-content class="p-6 pt-8">
          <div class="flex items-center gap-4">
            <div class="p-3 bg-blue-500/10 rounded-xl group-hover:bg-blue-500/20 transition-colors duration-300">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="w-8 h-8 text-blue-500">
                <rect width="20" height="14" x="2" y="5" rx="2" />
                <line x1="2" x2="22" y1="10" y2="10" />
              </svg>
            </div>
            <div>
              <p class="text-sm font-medium text-muted-foreground">Revenue Today</p>
              <h3 class="text-2xl font-bold text-blue-500 mt-1">{{ number_format($stats['revenue'], 2) }} <span
                  class="text-sm font-normal text-muted-foreground">MAD</span></h3>
            </div>
          </div>
        </x-shadcn.card-content>
      </x-shadcn.card>

      <!-- Bookings Card -->
      <x-shadcn.card
        class="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 group">
        <div
          class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-green-500/10 rounded-full group-hover:scale-110 transition-transform duration-300">
        </div>
        <x-shadcn.card-content class="p-6 pt-8">
          <div class="flex items-center gap-4">
            <div class="p-3 bg-green-500/10 rounded-xl group-hover:bg-green-500/20 transition-colors duration-300">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="w-8 h-8 text-green-500">
                <path d="M8 2v4" />
                <path d="M16 2v4" />
                <rect width="18" height="18" x="3" y="4" rx="2" />
                <path d="M3 10h18" />
                <path d="M10 16h4" />
              </svg>
            </div>
            <div>
              <p class="text-sm font-medium text-muted-foreground">New Bookings</p>
              <h3 class="text-2xl font-bold text-green-500 mt-1">{{ $stats['bookings'] }}</h3>
            </div>
          </div>
        </x-shadcn.card-content>
      </x-shadcn.card>
    </div>

    {{-- Recent Bookings Table --}}
    <x-shadcn.card class="shadow-lg">
      <x-shadcn.card-header>
        <x-shadcn.card-title>Recent Bookings</x-shadcn.card-title>
      </x-shadcn.card-header>
      <x-shadcn.card-content>
        <x-shadcn.table responsive>
          <x-shadcn.table-header>
            <x-shadcn.table-row>
              <x-shadcn.table-head>Booking #</x-shadcn.table-head>
              <x-shadcn.table-head>Event</x-shadcn.table-head>
              <x-shadcn.table-head>Hotel</x-shadcn.table-head>
              <x-shadcn.table-head>Amount</x-shadcn.table-head>
              <x-shadcn.table-head>Status</x-shadcn.table-head>
            </x-shadcn.table-row>
          </x-shadcn.table-header>
          <x-shadcn.table-body>
            @foreach($stats['recent'] as $booking)
              <x-shadcn.table-row hover>
                <x-shadcn.table-cell>#{{ $booking->id }}</x-shadcn.table-cell>
                <x-shadcn.table-cell>{{ $booking->accommodation->name ?? 'N/A' }}</x-shadcn.table-cell>
                <x-shadcn.table-cell>{{ $booking->hotel->name ?? 'N/A' }}</x-shadcn.table-cell>
                <x-shadcn.table-cell>{{ $booking->package->prix_ttc ?? 0 }} MAD</x-shadcn.table-cell>
                <x-shadcn.table-cell>
                  <x-shadcn.badge
                    variant="{{ $booking->status == 'confirmed' ? 'default' : ($booking->status == 'pending' ? 'secondary' : 'destructive') }}">
                    {{ ucfirst($booking->status) }}
                  </x-shadcn.badge>
                </x-shadcn.table-cell>
              </x-shadcn.table-row>
            @endforeach
          </x-shadcn.table-body>
        </x-shadcn.table>
      </x-shadcn.card-content>
    </x-shadcn.card>
  </div>
@endsection