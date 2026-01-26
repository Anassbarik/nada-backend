@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">Organizer Dashboard</h1>
        <div class="flex gap-4 flex-wrap">
            <a href="{{ route('organizer.commissions') }}" class="text-logo-link hover:underline inline-flex items-center">
                View Commissions →
            </a>
            <a href="{{ route('organizer.flights') }}" class="text-logo-link hover:underline inline-flex items-center">
                View Flights →
            </a>
            <a href="{{ route('organizer.bookings') }}" class="text-logo-link hover:underline inline-flex items-center">
                View Bookings →
            </a>
        </div>
    </div>

    {{-- Event Info --}}
    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-header>
            <x-shadcn.card-title>Event Information</x-shadcn.card-title>
        </x-shadcn.card-header>
        <x-shadcn.card-content class="p-6">
            <div class="space-y-4">
                <div>
                    <h2 class="text-xl font-bold">{{ $event->name }}</h2>
                    @if($event->venue)
                        <p class="text-gray-600">{{ $event->venue }}</p>
                    @endif
                    @if($event->start_date && $event->end_date)
                        <p class="text-gray-600">{{ $event->start_date->format('F d, Y') }} - {{ $event->end_date->format('F d, Y') }}</p>
                    @endif
                </div>
            </div>
        </x-shadcn.card-content>
    </x-shadcn.card>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6">
        <x-shadcn.card class="border-0 shadow-lg hover:shadow-xl transition-all" style="border-top: 4px solid #00adf1;">
            <x-shadcn.card-content class="p-8 text-center">
                <div class="text-xl sm:text-2xl lg:text-3xl font-bold mb-2 break-words" style="color: #00adf1;">{{ $stats['total_bookings'] }}</div>
                <div class="text-xs sm:text-sm text-muted-foreground font-medium">Total Bookings</div>
            </x-shadcn.card-content>
        </x-shadcn.card>
        
        <x-shadcn.card class="border-0 shadow-lg hover:shadow-xl transition-all" style="border-top: 4px solid #8b5cf6;">
            <x-shadcn.card-content class="p-8 text-center">
                <div class="text-xl sm:text-2xl lg:text-3xl font-bold mb-2 break-words" style="color: #8b5cf6;">{{ $flightsCount ?? 0 }}</div>
                <div class="text-xs sm:text-sm text-muted-foreground font-medium">Total Flights</div>
            </x-shadcn.card-content>
        </x-shadcn.card>
        
        <x-shadcn.card class="border-0 shadow-lg hover:shadow-xl transition-all" style="border-top: 4px solid #83ce2f;">
            <x-shadcn.card-content class="p-4 sm:p-6 lg:p-8 text-center">
                <div class="text-xl sm:text-2xl lg:text-3xl font-bold mb-2 break-words" style="color: #83ce2f;">{{ $stats['confirmed_bookings'] }}</div>
                <div class="text-xs sm:text-sm text-muted-foreground font-medium">Confirmed</div>
            </x-shadcn.card-content>
        </x-shadcn.card>

        <x-shadcn.card class="border-0 shadow-lg hover:shadow-xl transition-all" style="border-top: 4px solid #f7cb00;">
            <x-shadcn.card-content class="p-4 sm:p-6 lg:p-8 text-center">
                <div class="text-xl sm:text-2xl lg:text-3xl font-bold mb-2 break-words" style="color: #f7cb00;">{{ $stats['pending_bookings'] }}</div>
                <div class="text-xs sm:text-sm text-muted-foreground font-medium">Pending</div>
            </x-shadcn.card-content>
        </x-shadcn.card>

        <x-shadcn.card class="border-0 shadow-lg hover:shadow-xl transition-all" style="border-top: 4px solid #ea5d25;">
            <x-shadcn.card-content class="p-4 sm:p-6 lg:p-8 text-center">
                <div class="text-xl sm:text-2xl lg:text-3xl font-bold mb-2 break-words" style="color: #ea5d25;">{{ number_format($stats['total_revenue'], 2) }} MAD</div>
                <div class="text-xs sm:text-sm text-muted-foreground font-medium">Total Revenue</div>
            </x-shadcn.card-content>
        </x-shadcn.card>

        <x-shadcn.card class="border-0 shadow-lg hover:shadow-xl transition-all" style="border-top: 4px solid #10b981;">
            <x-shadcn.card-content class="p-4 sm:p-6 lg:p-8 text-center">
                <div class="text-xl sm:text-2xl lg:text-3xl font-bold mb-2 break-words" style="color: #10b981;">{{ number_format($stats['total_commission'] ?? 0, 2) }} MAD</div>
                <div class="text-xs sm:text-sm text-muted-foreground font-medium">Total Commission</div>
                @if($stats['commission_percentage'] > 0)
                    <p class="text-xs text-gray-500 mt-1">{{ number_format($stats['commission_percentage'], 2) }}% rate</p>
                @endif
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
                        <x-shadcn.table-head>Guest Name</x-shadcn.table-head>
                        <x-shadcn.table-head>Hotel</x-shadcn.table-head>
                        <x-shadcn.table-head>Amount</x-shadcn.table-head>
                        <x-shadcn.table-head>Status</x-shadcn.table-head>
                        <x-shadcn.table-head>Actions</x-shadcn.table-head>
                    </x-shadcn.table-row>
                </x-shadcn.table-header>
                <x-shadcn.table-body>
                    @forelse($recentBookings as $booking)
                    <x-shadcn.table-row hover>
                        <x-shadcn.table-cell>#{{ $booking->booking_reference }}</x-shadcn.table-cell>
                        <x-shadcn.table-cell>{{ $booking->full_name ?? $booking->guest_name ?? 'N/A' }}</x-shadcn.table-cell>
                        <x-shadcn.table-cell>{{ $booking->hotel->name ?? 'N/A' }}</x-shadcn.table-cell>
                        <x-shadcn.table-cell>{{ number_format($booking->price ?? 0, 2) }} MAD</x-shadcn.table-cell>
                        <x-shadcn.table-cell>
                            <x-shadcn.badge variant="{{ $booking->status == 'confirmed' ? 'default' : ($booking->status == 'pending' ? 'secondary' : 'destructive') }}">
                                {{ ucfirst($booking->status) }}
                            </x-shadcn.badge>
                        </x-shadcn.table-cell>
                        <x-shadcn.table-cell>
                            @if($booking->voucher)
                                <a href="{{ route('organizer.bookings.voucher', $booking) }}" 
                                   class="text-logo-link hover:underline text-sm">
                                    Download Voucher
                                </a>
                            @else
                                <span class="text-gray-400 text-sm">No voucher</span>
                            @endif
                        </x-shadcn.table-cell>
                    </x-shadcn.table-row>
                    @empty
                    <x-shadcn.table-row>
                        <x-shadcn.table-cell colspan="6" class="text-center text-gray-500">
                            No bookings yet
                        </x-shadcn.table-cell>
                    </x-shadcn.table-row>
                    @endforelse
                </x-shadcn.table-body>
            </x-shadcn.table>
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>
@endsection

