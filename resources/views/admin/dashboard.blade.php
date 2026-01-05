@extends('layouts.admin')

@section('content')
<div class="space-y-8">
  {{-- Stats Cards --}}
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <x-shadcn.card class="border-0 shadow-lg hover:shadow-xl transition-all" style="border-top: 4px solid #00adf1;">
      <x-shadcn.card-content class="p-8 text-center">
        <div class="text-3xl font-bold mb-2" style="color: #00adf1;">{{ number_format($stats['revenue'], 2) }} MAD</div>
        <div class="text-muted-foreground font-medium">Revenue Today</div>
      </x-shadcn.card-content>
    </x-shadcn.card>
    
    <x-shadcn.card class="border-0 shadow-lg hover:shadow-xl transition-all" style="border-top: 4px solid #83ce2f;">
      <x-shadcn.card-content class="p-8 text-center">
        <div class="text-3xl font-bold mb-2" style="color: #83ce2f;">{{ $stats['bookings'] }}</div>
        <div class="text-muted-foreground font-medium">New Bookings</div>
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

