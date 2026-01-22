@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">Bookings - {{ $event->name }}</h1>
        <a href="{{ route('organizer.dashboard') }}" class="text-logo-link hover:underline inline-flex items-center">
            ← Back to Dashboard
        </a>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6">
            <x-shadcn.table responsive>
                <x-shadcn.table-header>
                    <x-shadcn.table-row>
                        <x-shadcn.table-head>Booking #</x-shadcn.table-head>
                        <x-shadcn.table-head>Guest Name</x-shadcn.table-head>
                        <x-shadcn.table-head>Email</x-shadcn.table-head>
                        <x-shadcn.table-head>Hotel</x-shadcn.table-head>
                        <x-shadcn.table-head>Package</x-shadcn.table-head>
                        <x-shadcn.table-head>Flight</x-shadcn.table-head>
                        <x-shadcn.table-head>Amount</x-shadcn.table-head>
                        <x-shadcn.table-head>Status</x-shadcn.table-head>
                        <x-shadcn.table-head>Date</x-shadcn.table-head>
                        <x-shadcn.table-head>Actions</x-shadcn.table-head>
                    </x-shadcn.table-row>
                </x-shadcn.table-header>
                <x-shadcn.table-body>
                    @forelse($bookings as $booking)
                    <x-shadcn.table-row hover>
                        <x-shadcn.table-cell>#{{ $booking->booking_reference }}</x-shadcn.table-cell>
                        <x-shadcn.table-cell>{{ $booking->full_name ?? $booking->guest_name ?? 'N/A' }}</x-shadcn.table-cell>
                        <x-shadcn.table-cell>{{ $booking->email ?? $booking->guest_email ?? 'N/A' }}</x-shadcn.table-cell>
                        <x-shadcn.table-cell>{{ $booking->hotel->name ?? 'N/A' }}</x-shadcn.table-cell>
                        <x-shadcn.table-cell>{{ $booking->package->nom_package ?? 'N/A' }}</x-shadcn.table-cell>
                        <x-shadcn.table-cell>
                            @if($booking->flight)
                                <div class="text-sm">
                                    <div class="font-medium">{{ $booking->flight->departure_flight_number }}</div>
                                    <div class="text-xs text-muted-foreground">{{ $booking->flight->reference }}</div>
                                </div>
                            @else
                                <span class="text-gray-400 text-sm">—</span>
                            @endif
                        </x-shadcn.table-cell>
                        <x-shadcn.table-cell>{{ number_format($booking->price ?? 0, 2) }} MAD</x-shadcn.table-cell>
                        <x-shadcn.table-cell>
                            <x-shadcn.badge variant="{{ $booking->status == 'confirmed' ? 'default' : ($booking->status == 'pending' ? 'secondary' : 'destructive') }}">
                                {{ ucfirst($booking->status) }}
                            </x-shadcn.badge>
                        </x-shadcn.table-cell>
                        <x-shadcn.table-cell>{{ $booking->created_at->format('M d, Y') }}</x-shadcn.table-cell>
                        <x-shadcn.table-cell>
                            @if($booking->voucher)
                                <a href="{{ route('organizer.bookings.voucher', $booking) }}" 
                                   class="text-logo-link hover:underline text-sm inline-flex items-center">
                                    <i data-lucide="download" class="w-4 h-4 mr-1"></i>
                                    Download Voucher
                                </a>
                            @else
                                <span class="text-gray-400 text-sm">No voucher</span>
                            @endif
                        </x-shadcn.table-cell>
                    </x-shadcn.table-row>
                    @empty
                    <x-shadcn.table-row>
                        <x-shadcn.table-cell colspan="10" class="text-center text-gray-500">
                            No bookings found
                        </x-shadcn.table-cell>
                    </x-shadcn.table-row>
                    @endforelse
                </x-shadcn.table-body>
            </x-shadcn.table>

            <div class="mt-4">
                {{ $bookings->links() }}
            </div>
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>
@endsection

