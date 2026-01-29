@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">
                Flights
            </h1>
            <p class="text-gray-600 mt-1 text-sm">
                All flights across events. Each flight is associated with an event (accommodation) and shows its event name.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.standalone.flights.create') }}"
               class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-white shadow-sm"
               style="background-color: #00adf1;"
               onmouseover="this.style.backgroundColor='#0099d8'"
               onmouseout="this.style.backgroundColor='#00adf1'">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                Create Flight
            </a>
            <a href="{{ route('admin.standalone.flights.exportAll') }}"
               class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 shadow-sm">
                <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                Export All to Excel
            </a>
        </div>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6">
            @if($flights->count() === 0)
                <div class="text-center py-12 text-gray-500">
                    <p class="text-lg font-medium mb-2">No flights found.</p>
                    <p class="text-sm">Create flights from the Events → specific event → Flights section.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <x-shadcn.table responsive>
                        <x-shadcn.table-header>
                            <x-shadcn.table-row>
                                <x-shadcn.table-head>Reference</x-shadcn.table-head>
                                <x-shadcn.table-head>Event</x-shadcn.table-head>
                                <x-shadcn.table-head>Client Name</x-shadcn.table-head>
                                <x-shadcn.table-head>Class / Type</x-shadcn.table-head>
                                <x-shadcn.table-head>Departure</x-shadcn.table-head>
                                <x-shadcn.table-head>Return</x-shadcn.table-head>
                                <x-shadcn.table-head>Status</x-shadcn.table-head>
                                <x-shadcn.table-head class="text-right">Actions</x-shadcn.table-head>
                            </x-shadcn.table-row>
                        </x-shadcn.table-header>
                        <x-shadcn.table-body>
                            @foreach($flights as $flight)
                                <x-shadcn.table-row hover>
                                    <x-shadcn.table-cell class="font-medium">
                                        {{ $flight->reference }}
                                    </x-shadcn.table-cell>
                                    <x-shadcn.table-cell class="text-sm">
                                        @if($flight->accommodation)
                                            <div class="font-medium">
                                                {{ $flight->accommodation->name }}
                                            </div>
                                            @if($flight->accommodation->compact_dates)
                                                <div class="text-xs text-gray-500">
                                                    {{ $flight->accommodation->compact_dates }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-xs text-red-500">No event linked</span>
                                        @endif
                                    </x-shadcn.table-cell>
                                    <x-shadcn.table-cell>
                                        {{ $flight->full_name }}
                                    </x-shadcn.table-cell>
                                    <x-shadcn.table-cell class="text-sm">
                                        <div class="flex flex-col gap-1">
                                            <x-shadcn.badge variant="outline" class="w-fit">
                                                {{ $flight->flight_class_label }}
                                            </x-shadcn.badge>
                                            <span class="text-xs text-gray-500">
                                                {{ $flight->flight_category_label }}
                                            </span>
                                        </div>
                                    </x-shadcn.table-cell>
                                    <x-shadcn.table-cell>
                                        <div class="text-sm">
                                            <div class="font-medium">
                                                {{ $flight->departure_flight_number }}
                                            </div>
                                            <div class="text-xs text-gray-500">
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
                                                <div class="font-medium">
                                                    {{ $flight->return_flight_number }}
                                                </div>
                                                <div class="text-xs text-gray-500">
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
                                            <span class="text-xs text-gray-400">One-way</span>
                                        @endif
                                    </x-shadcn.table-cell>
                                    <x-shadcn.table-cell>
                                        <x-shadcn.badge variant="{{ $flight->status === 'paid' ? 'default' : 'secondary' }}">
                                            {{ $flight->status_label }}
                                        </x-shadcn.badge>
                                    </x-shadcn.table-cell>
                                    <x-shadcn.table-cell class="text-right">
                                        @if($flight->accommodation)
                                            <div class="flex justify-end gap-2">
                                                <a href="{{ route('admin.standalone.flights.show', $flight) }}" 
                                                   class="text-xs px-2 py-1 rounded-md bg-gray-100 text-gray-800 hover:bg-gray-200">
                                                    View
                                                </a>
                                                @if(auth()->user()->hasPermission('flights', 'edit'))
                                                    <a href="{{ route('admin.standalone.flights.edit', $flight) }}" 
                                                       class="text-xs px-2 py-1 rounded-md bg-blue-100 text-blue-700 hover:bg-blue-200">
                                                        Edit
                                                    </a>
                                                @endif
                                                @if(auth()->user()->hasPermission('flights', 'view'))
                                                    <a href="{{ route('admin.flights.exportSingle', [$flight->accommodation, $flight]) }}" 
                                                       class="text-xs px-2 py-1 rounded-md bg-green-100 text-green-700 hover:bg-green-200">
                                                        Export
                                                    </a>
                                                @endif
                                                @if(auth()->user()->hasPermission('flights', 'create'))
                                                    <form method="POST" action="{{ route('admin.standalone.flights.duplicate', $flight) }}"
                                                          onsubmit="return confirm('Duplicate this flight?');">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="text-xs px-2 py-1 rounded-md bg-yellow-100 text-yellow-800 hover:bg-yellow-200">
                                                            Duplicate
                                                        </button>
                                                    </form>
                                                @endif
                                                @if(auth()->user()->hasPermission('flights', 'delete'))
                                                    <form method="POST" action="{{ route('admin.standalone.flights.destroy', $flight) }}"
                                                          onsubmit="return confirm('Are you sure you want to delete this flight?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="text-xs px-2 py-1 rounded-md bg-red-100 text-red-700 hover:bg-red-200">
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endif
                                    </x-shadcn.table-cell>
                                </x-shadcn.table-row>
                            @endforeach
                        </x-shadcn.table-body>
                    </x-shadcn.table>
                </div>

                <div class="mt-4">
                    {{ $flights->links() }}
                </div>
            @endif
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>
@endsection


