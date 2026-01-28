@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">Commissions</h1>
        <div class="flex gap-4">
            <a href="{{ route('organizer.dashboard') }}" class="text-logo-link hover:underline inline-flex items-center">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Back to Dashboard
            </a>
        </div>
    </div>

    {{-- Commission Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-shadcn.card class="border-0 shadow-lg hover:shadow-xl transition-all" style="border-top: 4px solid #83ce2f;">
            <x-shadcn.card-content class="p-8 text-center">
                <div class="text-xl sm:text-2xl lg:text-3xl font-bold mb-2 break-words" style="color: #83ce2f;">
                    {{ number_format($totalCommission, 2) }} MAD
                </div>
                <div class="text-xs sm:text-sm text-muted-foreground font-medium">Total Commission Earned</div>
                <p class="text-xs text-gray-500 mt-2">From confirmed bookings</p>
            </x-shadcn.card-content>
        </x-shadcn.card>

        <x-shadcn.card class="border-0 shadow-lg hover:shadow-xl transition-all" style="border-top: 4px solid #f7cb00;">
            <x-shadcn.card-content class="p-8 text-center">
                <div class="text-xl sm:text-2xl lg:text-3xl font-bold mb-2 break-words" style="color: #f7cb00;">
                    {{ number_format($pendingCommission, 2) }} MAD
                </div>
                <div class="text-xs sm:text-sm text-muted-foreground font-medium">Pending Commission</div>
                <p class="text-xs text-gray-500 mt-2">From pending bookings</p>
            </x-shadcn.card-content>
        </x-shadcn.card>

        <x-shadcn.card class="border-0 shadow-lg hover:shadow-xl transition-all" style="border-top: 4px solid #00adf1;">
            <x-shadcn.card-content class="p-8 text-center">
                <div class="text-xl sm:text-2xl lg:text-3xl font-bold mb-2 break-words" style="color: #00adf1;">
                    {{ number_format($commissionPercentage, 2) }}%
                </div>
                <div class="text-xs sm:text-sm text-muted-foreground font-medium">Commission Rate</div>
                <p class="text-xs text-gray-500 mt-2">Set by admin</p>
            </x-shadcn.card-content>
        </x-shadcn.card>
    </div>

    {{-- Commission Details Table --}}
    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-header>
            <x-shadcn.card-title>Commission Details</x-shadcn.card-title>
            <p class="text-sm text-gray-500 mt-1">All bookings with commission earnings</p>
        </x-shadcn.card-header>
        <x-shadcn.card-content>
            <x-shadcn.table responsive>
                <x-shadcn.table-header>
                    <x-shadcn.table-row>
                        <x-shadcn.table-head>Booking #</x-shadcn.table-head>
                        <x-shadcn.table-head>Guest Name</x-shadcn.table-head>
                        <x-shadcn.table-head>Hotel</x-shadcn.table-head>
                        <x-shadcn.table-head>Booking Amount</x-shadcn.table-head>
                        <x-shadcn.table-head>Commission</x-shadcn.table-head>
                        <x-shadcn.table-head>Status</x-shadcn.table-head>
                        <x-shadcn.table-head>Date</x-shadcn.table-head>
                    </x-shadcn.table-row>
                </x-shadcn.table-header>
                <x-shadcn.table-body>
                    @forelse($bookings as $booking)
                    <x-shadcn.table-row hover>
                        <x-shadcn.table-cell>#{{ $booking->booking_reference }}</x-shadcn.table-cell>
                        <x-shadcn.table-cell>{{ $booking->full_name ?? $booking->guest_name ?? 'N/A' }}</x-shadcn.table-cell>
                        <x-shadcn.table-cell>{{ $booking->hotel->name ?? 'N/A' }}</x-shadcn.table-cell>
                        <x-shadcn.table-cell>{{ number_format($booking->price ?? 0, 2) }} MAD</x-shadcn.table-cell>
                        <x-shadcn.table-cell>
                            <span class="font-semibold" style="color: #83ce2f;">
                                {{ number_format($booking->commission_amount ?? 0, 2) }} MAD
                            </span>
                        </x-shadcn.table-cell>
                        <x-shadcn.table-cell>
                            <x-shadcn.badge variant="{{ $booking->status == 'confirmed' ? 'default' : ($booking->status == 'pending' ? 'secondary' : 'destructive') }}">
                                {{ ucfirst($booking->status) }}
                            </x-shadcn.badge>
                        </x-shadcn.table-cell>
                        <x-shadcn.table-cell>
                            {{ $booking->created_at->format('M d, Y') }}
                        </x-shadcn.table-cell>
                    </x-shadcn.table-row>
                    @empty
                    <x-shadcn.table-row>
                        <x-shadcn.table-cell colspan="7" class="text-center text-gray-500 py-8">
                            <div class="flex flex-col items-center">
                                <i data-lucide="dollar-sign" class="w-12 h-12 text-gray-400 mb-2"></i>
                                <p class="text-lg font-medium">No commissions yet</p>
                                <p class="text-sm text-gray-400 mt-1">Commissions will appear here once bookings are made for your event.</p>
                            </div>
                        </x-shadcn.table-cell>
                    </x-shadcn.table-row>
                    @endforelse
                </x-shadcn.table-body>
            </x-shadcn.table>
            
            @if($bookings->hasPages())
                <div class="mt-4">
                    {{ $bookings->links() }}
                </div>
            @endif
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>
@endsection



