@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">Flights - {{ $event->name }}</h1>
        <a href="{{ route('organizer.dashboard') }}" class="text-logo-link hover:underline inline-flex items-center">
            ← Back to Dashboard
        </a>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6">
            <x-shadcn.table responsive>
                <x-shadcn.table-header>
                    <x-shadcn.table-row>
                        <x-shadcn.table-head>Reference</x-shadcn.table-head>
                        <x-shadcn.table-head>Client Name</x-shadcn.table-head>
                        <x-shadcn.table-head>Flight Class</x-shadcn.table-head>
                        <x-shadcn.table-head>Type</x-shadcn.table-head>
                        <x-shadcn.table-head>Departure</x-shadcn.table-head>
                        <x-shadcn.table-head>Return</x-shadcn.table-head>
                        <x-shadcn.table-head>Price</x-shadcn.table-head>
                        <x-shadcn.table-head>Status</x-shadcn.table-head>
                        <x-shadcn.table-head>Date</x-shadcn.table-head>
                    </x-shadcn.table-row>
                </x-shadcn.table-header>
                <x-shadcn.table-body>
                    @forelse($flights as $flight)
                    <x-shadcn.table-row hover>
                        <x-shadcn.table-cell class="font-medium">{{ $flight->reference }}</x-shadcn.table-cell>
                        <x-shadcn.table-cell>{{ $flight->full_name }}</x-shadcn.table-cell>
                        <x-shadcn.table-cell>
                            <x-shadcn.badge variant="outline">
                                {{ $flight->flight_class_label }}
                            </x-shadcn.badge>
                        </x-shadcn.table-cell>
                        <x-shadcn.table-cell>
                            <span class="text-xs">{{ $flight->flight_category_label }}</span>
                        </x-shadcn.table-cell>
                        <x-shadcn.table-cell>
                            <div class="text-sm">
                                <div class="font-medium">{{ $flight->departure_flight_number }}</div>
                                <div class="text-xs text-muted-foreground">
                                    {{ $flight->departure_date ? \Carbon\Carbon::parse($flight->departure_date)->format('d/m/Y') : '—' }} 
                                    @if($flight->departure_time)
                                        {{ \Carbon\Carbon::parse($flight->departure_time)->format('H:i') }}
                                    @endif
                                </div>
                                <div class="text-xs text-gray-400 mt-1">
                                    {{ $flight->departure_airport ?? '—' }} → {{ $flight->arrival_airport ?? '—' }}
                                </div>
                            </div>
                        </x-shadcn.table-cell>
                        <x-shadcn.table-cell>
                            @if($flight->return_flight_number)
                                <div class="text-sm">
                                    <div class="font-medium">{{ $flight->return_flight_number }}</div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ $flight->return_date ? \Carbon\Carbon::parse($flight->return_date)->format('d/m/Y') : '—' }}
                                        @if($flight->return_departure_time)
                                            {{ \Carbon\Carbon::parse($flight->return_departure_time)->format('H:i') }}
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        {{ $flight->return_departure_airport ?? '—' }} → {{ $flight->return_arrival_airport ?? '—' }}
                                    </div>
                                </div>
                            @else
                                <span class="text-gray-400 text-xs">One-way</span>
                            @endif
                        </x-shadcn.table-cell>
                        <x-shadcn.table-cell>
                            @if($event->show_flight_prices_organizer_dashboard ?? true)
                                <div class="text-sm">
                                    @if($flight->flight_category === 'round_trip')
                                        <div>{{ number_format($flight->departure_price_ttc ?? 0, 2) }} + {{ number_format($flight->return_price_ttc ?? 0, 2) }}</div>
                                        <div class="font-medium">{{ number_format($flight->total_price, 2) }} MAD</div>
                                    @else
                                        <div class="font-medium">{{ number_format($flight->departure_price_ttc ?? 0, 2) }} MAD</div>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400 text-sm">Price hidden</span>
                            @endif
                        </x-shadcn.table-cell>
                        <x-shadcn.table-cell>
                            <x-shadcn.badge variant="{{ $flight->status === 'paid' ? 'default' : 'secondary' }}">
                                {{ $flight->status === 'paid' ? 'Paid' : 'Pending' }}
                            </x-shadcn.badge>
                        </x-shadcn.table-cell>
                        <x-shadcn.table-cell class="text-sm">{{ $flight->created_at->format('M d, Y') }}</x-shadcn.table-cell>
                    </x-shadcn.table-row>
                    @empty
                    <x-shadcn.table-row>
                        <x-shadcn.table-cell colspan="9" class="text-center text-gray-500">
                            No flights found
                        </x-shadcn.table-cell>
                    </x-shadcn.table-row>
                    @endforelse
                </x-shadcn.table-body>
            </x-shadcn.table>

            <div class="mt-4">
                {{ $flights->links() }}
            </div>
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>
@endsection

