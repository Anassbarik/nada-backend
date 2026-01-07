@extends('layouts.admin')

@section('content')
@php($booking = $invoice->booking)

<div class="space-y-6">
  <div class="flex items-start justify-between gap-4">
    <div>
      <h1 class="text-4xl font-bold">Invoice {{ $invoice->invoice_number }}</h1>
      <div class="text-sm text-muted-foreground">
        Booking: {{ $booking?->booking_reference ?? $invoice->booking_id }}
      </div>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('admin.invoices.pdf', $invoice) }}" target="_blank" class="btn-logo-primary px-4 py-2 rounded-md font-semibold transition-all">Open PDF</a>
      <a href="{{ route('admin.invoices.edit', $invoice) }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">Edit</a>
    </div>
  </div>

  <x-shadcn.card class="shadow-lg">
    <x-shadcn.card-content class="p-6 space-y-3">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <div class="text-xs text-muted-foreground">Guest</div>
          <div class="font-medium">{{ $booking?->full_name ?? $booking?->guest_name ?? '—' }}</div>
          <div class="text-sm text-muted-foreground">{{ $booking?->guest_email ?: ($booking?->email ?? '—') }}</div>
        </div>
        <div>
          <div class="text-xs text-muted-foreground">Status</div>
          <x-shadcn.badge variant="{{ $invoice->status === 'paid' ? 'default' : ($invoice->status === 'sent' ? 'secondary' : 'outline') }}">
            {{ strtoupper($invoice->status) }}
          </x-shadcn.badge>
        </div>
        <div>
          <div class="text-xs text-muted-foreground">Total</div>
          <div class="font-semibold">{{ number_format((float) $invoice->total_amount, 2, '.', '') }} MAD</div>
        </div>
        <div>
          <div class="text-xs text-muted-foreground">PDF Path</div>
          <div class="text-sm">{{ $invoice->pdf_path ?? '—' }}</div>
        </div>
      </div>

      @if(!empty($invoice->notes))
        <div class="pt-2">
          <div class="text-xs text-muted-foreground">Notes</div>
          <div class="whitespace-pre-wrap">{{ $invoice->notes }}</div>
        </div>
      @endif
    </x-shadcn.card-content>
  </x-shadcn.card>

  <x-shadcn.card class="shadow-lg">
    <x-shadcn.card-content class="p-6">
      <h2 class="text-xl font-semibold mb-4">Actions</h2>
      <div class="flex items-center gap-3">
        <form method="POST" action="{{ route('admin.invoices.send', $invoice) }}">
          @csrf
          <button type="submit" class="btn-logo-primary px-4 py-2 rounded-md font-semibold transition-all">Envoyer Facture</button>
        </form>
        <form method="POST" action="{{ route('admin.invoices.destroy', $invoice) }}" onsubmit="return confirm('Delete this invoice?');">
          @csrf
          @method('DELETE')
          <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">Delete</button>
        </form>
      </div>
    </x-shadcn.card-content>
  </x-shadcn.card>
</div>
@endsection


