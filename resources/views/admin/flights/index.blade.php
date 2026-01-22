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
                <div class="text-gray-500">{{ $flight->departure_date->format('Y-m-d') }}</div>
                <div class="text-xs text-gray-400">{{ $flight->departure_airport ?? '—' }} → {{ $flight->arrival_airport ?? '—' }}</div>
              </div>
            </x-shadcn.table-cell>
            <x-shadcn.table-cell>
              @if($flight->return_date)
                <div class="text-sm">
                  <div class="font-medium">{{ $flight->return_flight_number }}</div>
                  <div class="text-gray-500">{{ $flight->return_date->format('Y-m-d') }}</div>
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

