@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">{{ __('Night Prices for') }}: {{ $hotel->name }}</h1>
        <a href="{{ route('admin.hotels.night-prices.create', $hotel) }}" class="btn-logo-primary text-white px-4 sm:px-6 lg:px-8 py-2 sm:py-3 rounded-xl font-semibold transition-all text-sm sm:text-base whitespace-nowrap">
            Add Night Price
        </a>
    </div>

    <div class="mb-4">
        <a href="{{ route('admin.events.hotels.index', $hotel->event) }}" class="text-logo-link hover:underline inline-flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Back to Hotels
        </a>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-0">
            @if($nightPrices->count() > 0)
                <x-shadcn.table responsive>
                    <x-shadcn.table-header>
                        <x-shadcn.table-row>
                            <x-shadcn.table-head>Price per Night</x-shadcn.table-head>
                            <x-shadcn.table-head>Valid From</x-shadcn.table-head>
                            <x-shadcn.table-head>Valid To</x-shadcn.table-head>
                            <x-shadcn.table-head>Status</x-shadcn.table-head>
                            <x-shadcn.table-head>Actions</x-shadcn.table-head>
                        </x-shadcn.table-row>
                    </x-shadcn.table-header>
                    <x-shadcn.table-body>
                        @foreach($nightPrices as $nightPrice)
                            <x-shadcn.table-row hover>
                                <x-shadcn.table-cell class="font-medium">${{ number_format($nightPrice->price_per_night, 2) }}/night</x-shadcn.table-cell>
                                <x-shadcn.table-cell>{{ $nightPrice->valid_from->format('M d, Y') }}</x-shadcn.table-cell>
                                <x-shadcn.table-cell>{{ $nightPrice->valid_to ? $nightPrice->valid_to->format('M d, Y') : 'No end date' }}</x-shadcn.table-cell>
                                <x-shadcn.table-cell>
                                    <x-shadcn.badge variant="{{ $nightPrice->status === 'active' ? 'default' : 'secondary' }}">
                                        {{ ucfirst($nightPrice->status) }}
                                    </x-shadcn.badge>
                                </x-shadcn.table-cell>
                                <x-shadcn.table-cell class="space-x-2">
                                    <a href="{{ route('admin.hotels.night-prices.edit', [$hotel, $nightPrice]) }}" class="text-logo-link hover:underline">Edit</a>
                                    <form method="POST" action="{{ route('admin.hotels.night-prices.destroy', [$hotel, $nightPrice]) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this night price?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                </x-shadcn.table-cell>
                            </x-shadcn.table-row>
                        @endforeach
                    </x-shadcn.table-body>
                </x-shadcn.table>
            @else
                <div class="text-center py-8 p-6">
                    <p class="text-gray-500 mb-4">No night prices set for this hotel.</p>
                    <a href="{{ route('admin.hotels.night-prices.create', $hotel) }}" class="btn-logo-primary text-white px-8 py-3 rounded-xl font-semibold transition-all inline-block">
                        Set Base Night Price
                    </a>
                </div>
            @endif
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>
@endsection
