@extends('layouts.admin')

@section('content')
@php($booking = $invoice->booking)

<div class="space-y-6">
  <div class="flex items-start justify-between gap-4">
    <div>
      <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">Edit Invoice {{ $invoice->invoice_number }}</h1>
      <div class="text-sm text-muted-foreground">
        Booking: {{ $booking?->booking_reference ?? $invoice->booking_id }}
      </div>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('admin.invoices.show', $invoice) }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">Back</a>
    </div>
  </div>

  <x-shadcn.card class="shadow-lg">
    <x-shadcn.card-content class="p-6">
      <form method="POST" action="{{ route('admin.invoices.update', $invoice) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
          <label class="block text-sm font-medium mb-1">Total Amount (MAD)</label>
          <input name="total_amount" type="number" step="0.01" min="0"
                 value="{{ old('total_amount', $invoice->total_amount) }}"
                 class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2">
          @error('total_amount') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Status</label>
          <select name="status" class="w-full bg-white text-gray-900 border border-gray-300 rounded-md shadow-sm px-3 py-2">
            @foreach(['draft' => 'Draft', 'sent' => 'Sent', 'paid' => 'Paid'] as $value => $label)
              <option value="{{ $value }}" @selected(old('status', $invoice->status) === $value)>{{ $label }}</option>
            @endforeach
          </select>
          @error('status') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Notes</label>
          <textarea name="notes" rows="6" class="w-full bg-white text-gray-900 border border-gray-300 rounded-md shadow-sm px-3 py-2">{{ old('notes', $invoice->notes) }}</textarea>
          @error('notes') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="flex items-center gap-3">
          <button type="submit" class="btn-logo-primary px-4 py-2 rounded-md font-semibold transition-all">Save</button>
          <a href="{{ route('admin.invoices.pdf', $invoice) }}" target="_blank" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-md hover:bg-gray-200 transition-colors">Preview PDF</a>
        </div>
      </form>
    </x-shadcn.card-content>
  </x-shadcn.card>
</div>
@endsection


