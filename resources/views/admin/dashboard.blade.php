@extends('layouts.admin')

@section('content')
<div class="space-y-8">
  {{-- Maintenance Mode Toggle --}}
  <x-shadcn.card class="shadow-lg border-l-4" style="border-left-color: #f59e0b;">
    <x-shadcn.card-content class="p-6">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex-1 min-w-0">
          <h3 class="text-base sm:text-lg font-semibold mb-1 break-words">Maintenance Mode</h3>
          <p class="text-xs sm:text-sm text-muted-foreground break-words">Control maintenance status for the frontend application</p>
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
    <x-shadcn.card class="border-0 shadow-lg hover:shadow-xl transition-all" style="border-top: 4px solid #00adf1;">
      <x-shadcn.card-content class="p-8 text-center">
        <div class="text-xl sm:text-2xl lg:text-3xl font-bold mb-2 break-words" style="color: #00adf1;">{{ number_format($stats['revenue'], 2) }} MAD</div>
        <div class="text-xs sm:text-sm text-muted-foreground font-medium">Revenue Today</div>
      </x-shadcn.card-content>
    </x-shadcn.card>
    
    <x-shadcn.card class="border-0 shadow-lg hover:shadow-xl transition-all" style="border-top: 4px solid #83ce2f;">
      <x-shadcn.card-content class="p-4 sm:p-6 lg:p-8 text-center">
        <div class="text-xl sm:text-2xl lg:text-3xl font-bold mb-2 break-words" style="color: #83ce2f;">{{ $stats['bookings'] }}</div>
        <div class="text-xs sm:text-sm text-muted-foreground font-medium">New Bookings</div>
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
            <x-shadcn.table-cell>{{ $booking->event->name ?? 'N/A' }}</x-shadcn.table-cell>
            <x-shadcn.table-cell>{{ $booking->hotel->name ?? 'N/A' }}</x-shadcn.table-cell>
            <x-shadcn.table-cell>{{ $booking->package->prix_ttc ?? 0 }} MAD</x-shadcn.table-cell>
            <x-shadcn.table-cell>
              <x-shadcn.badge variant="{{ $booking->status == 'confirmed' ? 'default' : ($booking->status == 'pending' ? 'secondary' : 'destructive') }}">
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

