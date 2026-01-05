@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold">{{ __('Add Night Price') }} - {{ $hotel->name }}</h1>
    </div>

    <div class="mb-4">
        <a href="{{ route('admin.hotels.night-prices.index', $hotel) }}" class="text-logo-link hover:underline inline-flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Back to Night Prices
        </a>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6">
            <form method="POST" action="{{ route('admin.hotels.night-prices.store', $hotel) }}">
                @csrf

                <div class="mb-4">
                    <x-input-label for="price_per_night" :value="__('Price per Night')" />
                    <x-text-input id="price_per_night" class="block mt-1 w-full" type="number" step="0.01" name="price_per_night" :value="old('price_per_night')" min="0" required autofocus />
                    <p class="mt-1 text-sm text-gray-500">Base price per night. Guests can book 1, 2, 3+ nights at this rate.</p>
                    <x-input-error :messages="$errors->get('price_per_night')" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <x-input-label for="valid_from" :value="__('Valid From')" />
                        <x-text-input id="valid_from" class="block mt-1 w-full" type="date" name="valid_from" :value="old('valid_from')" required />
                        <x-input-error :messages="$errors->get('valid_from')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="valid_to" :value="__('Valid To (Optional)')" />
                        <x-text-input id="valid_to" class="block mt-1 w-full" type="date" name="valid_to" :value="old('valid_to')" />
                        <p class="mt-1 text-sm text-gray-500">Leave empty for no end date</p>
                        <x-input-error :messages="$errors->get('valid_to')" class="mt-2" />
                    </div>
                </div>

                <div class="mb-4">
                    <x-input-label for="status" :value="__('Status')" />
                    <select id="status" name="status" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <a href="{{ route('admin.hotels.night-prices.index', $hotel) }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                    <x-primary-button class="btn-logo-primary">
                        {{ __('Create Night Price') }}
                    </x-primary-button>
                </div>
            </form>
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>
@endsection
