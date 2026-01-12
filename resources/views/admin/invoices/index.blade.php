@extends('layouts.admin')

@section('content')
<div class="space-y-6">
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">{{ __('Invoices') }}</h1>
  </div>

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
                <div class="break-words">{{ $booking?->booking_reference ?? $invoice->booking_id }}</div>
                <div class="text-xs text-muted-foreground break-words">{{ $booking?->full_name ?? $booking?->guest_name ?? '—' }}</div>
              </x-shadcn.table-cell>
              <x-shadcn.table-cell class="break-all">{{ $email ?? '—' }}</x-shadcn.table-cell>
              <x-shadcn.table-cell>{{ number_format((float) $invoice->total_amount, 2, '.', '') }} MAD</x-shadcn.table-cell>
              <x-shadcn.table-cell>
                <x-shadcn.badge variant="{{ $invoice->status === 'paid' ? 'default' : ($invoice->status === 'sent' ? 'secondary' : 'outline') }}">
                  {{ strtoupper($invoice->status) }}
                </x-shadcn.badge>
              </x-shadcn.table-cell>
              <x-shadcn.table-cell>{{ $invoice->created_at?->format('Y-m-d') }}</x-shadcn.table-cell>
              <x-shadcn.table-cell>
                <div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm">
                  <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-logo-link hover:underline whitespace-nowrap">View</a>
                  <a href="{{ route('admin.invoices.edit', $invoice) }}" class="text-logo-link hover:underline whitespace-nowrap">Edit</a>
                  <a href="{{ route('admin.invoices.pdf', $invoice) }}" target="_blank" class="text-logo-link hover:underline whitespace-nowrap">PDF</a>

                  <form method="POST" action="{{ route('admin.invoices.send', $invoice) }}" class="inline">
                    @csrf
                    <button type="submit" class="text-logo-link hover:underline whitespace-nowrap">Envoyer</button>
                  </form>

                  <form method="POST" action="{{ route('admin.invoices.destroy', $invoice) }}" class="inline" onsubmit="return confirm('Delete this invoice?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-800 transition-colors whitespace-nowrap">Delete</button>
                  </form>
                </div>
              </x-shadcn.table-cell>
            </x-shadcn.table-row>
          @empty
            <x-shadcn.table-row>
              <x-shadcn.table-cell colspan="7" class="text-center text-muted-foreground">No invoices found.</x-shadcn.table-cell>
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


