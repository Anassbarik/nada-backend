@extends('layouts.admin')

@section('content')
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">{{ __('Invoices') }}</h1>
    </div>

    {{-- Filter Card --}}
    <x-shadcn.card class="shadow-lg">
      <x-shadcn.card-content class="p-6">
        <form method="GET" action="{{ route('admin.invoices.index') }}"
          class="flex flex-col sm:flex-row gap-3 sm:gap-4 items-end">
          <div class="flex-1 w-full">
            <label for="accommodation_id" class="block text-sm font-medium text-gray-700 mb-1">Filter by Event</label>
            <select name="accommodation_id" id="accommodation_id"
              class="w-full bg-white text-gray-900 border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm sm:text-base focus:border-logo-link focus:ring-logo-link">
              <option value="">All Events</option>
              @foreach($accommodations as $acc)
                <option value="{{ $acc->id }}" {{ request('accommodation_id') == $acc->id ? 'selected' : '' }}>
                  {{ $acc->name }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="flex gap-2 w-full sm:w-auto">
            <button type="submit"
              class="text-white px-4 sm:px-6 py-2 rounded-md font-semibold transition-all text-sm sm:text-base whitespace-nowrap"
              style="background-color: #00adf1;" onmouseover="this.style.backgroundColor='#0099d8'"
              onmouseout="this.style.backgroundColor='#00adf1'">
              Filter
            </button>
            @if(request('accommodation_id'))
              <a href="{{ route('admin.invoices.index') }}"
                class="px-4 sm:px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-all text-sm sm:text-base text-center whitespace-nowrap">
                Clear
              </a>
            @endif
          </div>
        </form>
      </x-shadcn.card-content>
    </x-shadcn.card>

    {{-- Main Table Card --}}
    <x-shadcn.card class="shadow-lg">
      <x-shadcn.card-content class="p-0">
        <x-shadcn.table responsive>
          <x-shadcn.table-header>
            <x-shadcn.table-row>
              <x-shadcn.table-head>N°</x-shadcn.table-head>
              <x-shadcn.table-head>Booking</x-shadcn.table-head>
              <x-shadcn.table-head>Email</x-shadcn.table-head>
              <x-shadcn.table-head>Total</x-shadcn.table-head>
              <x-shadcn.table-head>Status</x-shadcn.table-head>
              <x-shadcn.table-head>Date</x-shadcn.table-head>
              <x-shadcn.table-head>Actions</x-shadcn.table-head>
            </x-shadcn.table-row>
          </x-shadcn.table-header>
          <x-shadcn.table-body>
            @forelse($invoices as $invoice)
              @php
                $booking = $invoice->booking;
                $email = $booking?->guest_email ?: $booking?->email;
              @endphp
              <x-shadcn.table-row hover>
                <x-shadcn.table-cell class="font-medium break-words">{{ $invoice->invoice_number }}</x-shadcn.table-cell>
                <x-shadcn.table-cell class="break-words">
                  <div class="break-words font-medium text-gray-900">
                    {{ $booking?->booking_reference ?? $invoice->booking_id }}</div>
                  <div class="text-xs text-muted-foreground break-words">
                    {{ $booking?->full_name ?? $booking?->guest_name ?? '—' }}</div>
                </x-shadcn.table-cell>
                <x-shadcn.table-cell class="break-all">{{ $email ?? '—' }}</x-shadcn.table-cell>
                <x-shadcn.table-cell>{{ number_format((float) $invoice->total_amount, 2, '.', '') }}
                  MAD</x-shadcn.table-cell>
                <x-shadcn.table-cell>
                  <x-shadcn.badge
                    variant="{{ $invoice->status === 'paid' ? 'default' : ($invoice->status === 'sent' ? 'secondary' : 'outline') }}">
                    {{ strtoupper($invoice->status) }}
                  </x-shadcn.badge>
                </x-shadcn.table-cell>
                <x-shadcn.table-cell>{{ $invoice->created_at?->format('Y-m-d') }}</x-shadcn.table-cell>
                <x-shadcn.table-cell>
                  <div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm">
                    <a href="{{ route('admin.invoices.show', $invoice) }}"
                      class="text-logo-link hover:underline whitespace-nowrap">View</a>
                    <a href="{{ route('admin.invoices.edit', $invoice) }}"
                      class="text-logo-link hover:underline whitespace-nowrap">Edit</a>
                    <a href="{{ route('admin.invoices.pdf', $invoice) }}" target="_blank"
                      class="text-logo-link hover:underline whitespace-nowrap">PDF</a>

                    <form method="POST" action="{{ route('admin.invoices.send', $invoice) }}" class="inline">
                      @csrf
                      <button type="submit" class="text-logo-link hover:underline whitespace-nowrap">Send</button>
                    </form>

                    <form method="POST" action="{{ route('admin.invoices.destroy', $invoice) }}" class="inline"
                      onsubmit="return confirm('Delete this invoice?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit"
                        class="text-red-600 hover:text-red-800 transition-colors whitespace-nowrap">Delete</button>
                    </form>
                  </div>
                </x-shadcn.table-cell>
              </x-shadcn.table-row>
            @empty
              <x-shadcn.table-row>
                <x-shadcn.table-cell colspan="7" class="text-center text-muted-foreground py-8">No invoices
                  found.</x-shadcn.table-cell>
              </x-shadcn.table-row>
            @endforelse
          </x-shadcn.table-body>
        </x-shadcn.table>
      </x-shadcn.card-content>
    </x-shadcn.card>

    <div class="mt-4">
      {{ $invoices->links() }}
    </div>
  </div>
@endsection