@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">
                Flights - {{ $accommodation->name }}
            </h1>
            @if($accommodation->compact_dates)
                <p class="text-gray-600 mt-1 text-sm">
                    {{ $accommodation->compact_dates }}
                </p>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.events.index') }}" class="text-logo-link hover:underline inline-flex items-center">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Back to Events
            </a>
            @if(auth()->user()->hasPermission('flights', 'create') && $accommodation->canManageFlightsBy(auth()->user()))
                <a href="{{ route('admin.flights.create', $accommodation) }}" 
                   class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium btn-logo-primary shadow-sm">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                    Create Flight
                </a>
            @endif
            @if($accommodation->canManageFlightsBy(auth()->user()))
                <a href="{{ route('admin.flights.exportAccommodation', $accommodation) }}"
                   class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 shadow-sm">
                    <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                    Export Flights to Excel
                </a>
            @endif
        </div>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6">
            @if($flights->count() === 0)
                <div class="text-center py-12 text-gray-500">
                    <p class="text-lg font-medium mb-2">No flights found for this event yet.</p>
                    @if(auth()->user()->hasPermission('flights', 'create') && $accommodation->canManageFlightsBy(auth()->user()))
                        <p class="mb-4">Create the first flight for <span class="font-semibold">{{ $accommodation->name }}</span>.</p>
                        <a href="{{ route('admin.flights.create', $accommodation) }}" 
                           class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium btn-logo-primary shadow-sm">
                            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                            Create Flight
                        </a>
                    @endif
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
                                <x-shadcn.table-head>Price</x-shadcn.table-head>
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
                                        {{ $accommodation->name }}
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
                                        <div class="text-sm">
                                            @if($flight->flight_category === 'round_trip')
                                                <div>
                                                    {{ number_format($flight->departure_price_ttc ?? 0, 2) }} + {{ number_format($flight->return_price_ttc ?? 0, 2) }}
                                                </div>
                                                <div class="font-semibold">
                                                    {{ number_format($flight->total_price, 2) }} MAD
                                                </div>
                                            @else
                                                <div class="font-semibold">
                                                    {{ number_format($flight->departure_price_ttc ?? 0, 2) }} MAD
                                                </div>
                                            @endif
                                        </div>
                                    </x-shadcn.table-cell>
                                    <x-shadcn.table-cell>
                                        <x-shadcn.badge variant="{{ $flight->status === 'paid' ? 'default' : 'secondary' }}">
                                            {{ $flight->status_label }}
                                        </x-shadcn.badge>
                                    </x-shadcn.table-cell>
                                    <x-shadcn.table-cell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.flights.show', [$accommodation, $flight]) }}" 
                                               class="text-xs px-2 py-1 rounded-md bg-gray-100 text-gray-800 hover:bg-gray-200">
                                                View
                                            </a>
                                            @if(auth()->user()->hasPermission('flights', 'edit'))
                                                <a href="{{ route('admin.flights.edit', [$accommodation, $flight]) }}" 
                                                   class="text-xs px-2 py-1 rounded-md bg-blue-100 text-blue-700 hover:bg-blue-200">
                                                    Edit
                                                </a>
                                            @endif
                                            @if($accommodation->canManageFlightsBy(auth()->user()))
                                                <a href="{{ route('admin.flights.exportSingle', [$accommodation, $flight]) }}" 
                                                   class="text-xs px-2 py-1 rounded-md bg-green-100 text-green-700 hover:bg-green-200">
                                                    Export
                                                </a>
                                            @endif
                                            @if(auth()->user()->hasPermission('flights', 'create'))
                                                <form method="POST" action="{{ route('admin.flights.duplicate', [$accommodation, $flight]) }}"
                                                      onsubmit="return confirm('Duplicate this flight?');">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="text-xs px-2 py-1 rounded-md bg-yellow-100 text-yellow-800 hover:bg-yellow-200">
                                                        Duplicate
                                                    </button>
                                                </form>
                                            @endif
                                            @if(auth()->user()->hasPermission('flights', 'delete'))
                                                <form method="POST" action="{{ route('admin.flights.destroy', [$accommodation, $flight]) }}"
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

@extends('layouts.admin')

@section('content')
<div class="space-y-6">
  @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
      <span class="block sm:inline">{{ session('success') }}</span>
      @if(session('credentials_pdf_url'))
        <div class="mt-2">
          <a href="{{ session('credentials_pdf_url') }}" class="text-green-800 underline font-semibold" download>
            Download Credentials PDF
          </a>
        </div>
      @endif
    </div>
  @endif

  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
      <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">Flights Management</h1>
      <p class="text-gray-600 mt-1">{{ $accommodation->name }}</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('admin.events.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-all text-sm sm:text-base">
        Back to Accommodations
      </a>
      <a href="{{ route('admin.flights.create', $accommodation) }}" class="text-white px-4 sm:px-6 py-2 rounded-md font-semibold transition-all text-sm sm:text-base whitespace-nowrap" style="background-color: #00adf1;" onmouseover="this.style.backgroundColor='#0099d8'" onmouseout="this.style.backgroundColor='#00adf1'">
        + New Flight
      </a>
    </div>
  </div>
  
  <x-shadcn.card class="shadow-lg">
    <x-shadcn.card-content class="p-0">
      <x-shadcn.table responsive>
        <x-shadcn.table-header>
          <x-shadcn.table-row>
            <x-shadcn.table-head>Reference</x-shadcn.table-head>
            <x-shadcn.table-head>Client Name</x-shadcn.table-head>
            <x-shadcn.table-head>Flight Class</x-shadcn.table-head>
            <x-shadcn.table-head>Departure</x-shadcn.table-head>
            <x-shadcn.table-head>Return</x-shadcn.table-head>
            <x-shadcn.table-head>Status</x-shadcn.table-head>
            <x-shadcn.table-head>Payment</x-shadcn.table-head>
            <x-shadcn.table-head>Beneficiary</x-shadcn.table-head>
            <x-shadcn.table-head>Actions</x-shadcn.table-head>
          </x-shadcn.table-row>
        </x-shadcn.table-header>
        <x-shadcn.table-body>
          @forelse($flights as $flight)
          <x-shadcn.table-row hover>
            <x-shadcn.table-cell class="font-medium">{{ $flight->reference }}</x-shadcn.table-cell>
            <x-shadcn.table-cell>{{ $flight->full_name }}</x-shadcn.table-cell>
            <x-shadcn.table-cell>
              <x-shadcn.badge variant="outline">{{ $flight->flight_class_label }}</x-shadcn.badge>
            </x-shadcn.table-cell>
            <x-shadcn.table-cell>
              <div class="text-sm">
                <div class="font-medium">{{ $flight->departure_flight_number }}</div>
                <div class="text-gray-500">{{ $flight->departure_date ? \Carbon\Carbon::parse($flight->departure_date)->format('Y-m-d') : '—' }}</div>
                <div class="text-xs text-gray-400">{{ $flight->departure_airport ?? '—' }} → {{ $flight->arrival_airport ?? '—' }}</div>
              </div>
            </x-shadcn.table-cell>
            <x-shadcn.table-cell>
              @if($flight->return_date)
                <div class="text-sm">
                  <div class="font-medium">{{ $flight->return_flight_number }}</div>
                  <div class="text-gray-500">{{ \Carbon\Carbon::parse($flight->return_date)->format('Y-m-d') }}</div>
                  <div class="text-xs text-gray-400">{{ $flight->return_departure_airport ?? '—' }} → {{ $flight->return_arrival_airport ?? '—' }}</div>
                </div>
              @else
                <span class="text-gray-400">One-way</span>
              @endif
            </x-shadcn.table-cell>
            <x-shadcn.table-cell>
              <x-shadcn.badge variant="{{ $flight->status === 'paid' ? 'default' : 'secondary' }}">
                {{ $flight->status === 'paid' ? 'Paid' : 'Pending' }}
              </x-shadcn.badge>
            </x-shadcn.table-cell>
            <x-shadcn.table-cell>
              @if($flight->payment_method)
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
                <span class="text-xs px-2 py-1 rounded {{ $paymentTypeColors[$flight->payment_method] ?? 'bg-gray-100 text-gray-700' }}">
                  {{ $paymentTypeLabels[$flight->payment_method] ?? strtoupper($flight->payment_method) }}
                </span>
              @else
                <span class="text-xs text-gray-400">N/A</span>
              @endif
            </x-shadcn.table-cell>
            <x-shadcn.table-cell>
              @if($flight->beneficiary_type === 'organizer')
                <span class="text-blue-600">Organizer</span>
              @else
                <span class="text-green-600">Client</span>
                @if($flight->credentials_pdf_path)
                  <a href="{{ route('admin.flights.downloadCredentials', [$accommodation, $flight]) }}" 
                     class="ml-2 text-xs text-blue-600 hover:underline" 
                     title="Download Credentials PDF">
                    <i data-lucide="download" class="w-3 h-3 inline"></i>
                  </a>
                @endif
              @endif
            </x-shadcn.table-cell>
            <x-shadcn.table-cell>
              <div class="flex items-center gap-2">
                <a href="{{ route('admin.flights.show', [$accommodation, $flight]) }}" 
                   class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors"
                   title="View">
                  <i data-lucide="eye" class="w-4 h-4"></i>
                </a>
                <a href="{{ route('admin.flights.edit', [$accommodation, $flight]) }}" 
                   class="p-1.5 rounded-lg text-green-600 hover:bg-green-50 transition-colors"
                   title="Edit">
                  <i data-lucide="pencil" class="w-4 h-4"></i>
                </a>
                <form method="POST" action="{{ route('admin.flights.duplicate', [$accommodation, $flight]) }}" 
                      class="inline" 
                      onsubmit="return confirm('Are you sure you want to duplicate this flight?');">
                  @csrf
                  <button type="submit" 
                          class="p-1.5 rounded-lg text-orange-600 hover:bg-orange-50 transition-colors"
                          title="Duplicate">
                    <i data-lucide="copy" class="w-4 h-4"></i>
                  </button>
                </form>
                <form method="POST" action="{{ route('admin.flights.destroy', [$accommodation, $flight]) }}" 
                      class="inline" 
                      onsubmit="return confirm('Are you sure you want to delete this flight?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" 
                          class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 transition-colors"
                          title="Delete">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                  </button>
                </form>
              </div>
            </x-shadcn.table-cell>
          </x-shadcn.table-row>
          @empty
          <x-shadcn.table-row>
            <x-shadcn.table-cell colspan="9" class="text-center text-muted-foreground">
              No flights found. <a href="{{ route('admin.flights.create', $accommodation) }}" class="text-blue-600 hover:underline">Create one</a>
            </x-shadcn.table-cell>
          </x-shadcn.table-row>
          @endforelse
        </x-shadcn.table-body>
      </x-shadcn.table>
    </x-shadcn.card-content>
  </x-shadcn.card>
  
  <div class="mt-4">
    {{ $flights->links() }}
  </div>
</div>
@endsection

