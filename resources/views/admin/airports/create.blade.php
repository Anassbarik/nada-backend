@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold">Create New Airport - {{ $event->name }}</h1>
    </div>

    <div class="mb-4">
        <a href="{{ route('admin.events.airports.index', $event) }}" class="text-logo-link hover:underline inline-flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Back to Airports
        </a>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6">
            <form method="POST" action="{{ route('admin.events.airports.store', $event) }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <x-input-label for="name" value="Airport Name *" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus placeholder="e.g., Mohammed V International Airport" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="code" value="Airport Code" />
                        <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code')" maxlength="10" placeholder="e.g., CMN, RAK" />
                        <p class="mt-1 text-sm text-gray-500">IATA or ICAO code (optional)</p>
                        <x-input-error :messages="$errors->get('code')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="city" value="City" />
                        <x-text-input id="city" class="block mt-1 w-full" type="text" name="city" :value="old('city')" placeholder="e.g., Casablanca" />
                        <x-input-error :messages="$errors->get('city')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="country" value="Country" />
                        <x-text-input id="country" class="block mt-1 w-full" type="text" name="country" :value="old('country')" placeholder="e.g., Morocco" />
                        <x-input-error :messages="$errors->get('country')" class="mt-2" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="description" value="Description" />
                        <textarea id="description" name="description" rows="3" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                        <p class="mt-1 text-sm text-gray-500">Optional description or additional information</p>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="distance_from_venue" value="Distance from Venue" />
                        <x-text-input id="distance_from_venue" class="block mt-1 w-full" type="number" step="0.01" name="distance_from_venue" :value="old('distance_from_venue')" min="0" placeholder="0.00" />
                        <x-input-error :messages="$errors->get('distance_from_venue')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="distance_unit" value="Distance Unit" />
                        <select id="distance_unit" name="distance_unit" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="km" {{ old('distance_unit', 'km') === 'km' ? 'selected' : '' }}>Kilometers (km)</option>
                            <option value="miles" {{ old('distance_unit') === 'miles' ? 'selected' : '' }}>Miles</option>
                        </select>
                        <x-input-error :messages="$errors->get('distance_unit')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="sort_order" value="Sort Order" />
                        <x-text-input id="sort_order" class="block mt-1 w-full" type="number" name="sort_order" :value="old('sort_order', 0)" min="0" />
                        <p class="mt-1 text-sm text-gray-500">Lower numbers appear first</p>
                        <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
                    </div>

                    <div class="flex items-center">
                        <label class="flex items-center">
                            <input type="checkbox" name="active" value="1" {{ old('active', true) ? 'checked' : '' }} class="rounded border-gray-300">
                            <span class="ml-2 text-sm text-gray-600">Active</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end mt-6">
                    <a href="{{ route('admin.events.airports.index', $event) }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                    <x-primary-button class="btn-logo-primary">
                        Create Airport
                    </x-primary-button>
                </div>
            </form>
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>
@endsection

