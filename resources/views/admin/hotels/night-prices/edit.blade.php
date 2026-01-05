<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Night Price') }} - {{ $hotel->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('admin.hotels.night-prices.index', $hotel) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">← Back to Night Prices</a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.hotels.night-prices.update', [$hotel, $nightPrice]) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <x-input-label for="price_per_night" :value="__('Price per Night')" />
                            <x-text-input id="price_per_night" class="block mt-1 w-full" type="number" step="0.01" name="price_per_night" :value="old('price_per_night', $nightPrice->price_per_night)" min="0" required autofocus />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Base price per night. Guests can book 1, 2, 3+ nights at this rate.</p>
                            <x-input-error :messages="$errors->get('price_per_night')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="valid_from" :value="__('Valid From')" />
                                <x-text-input id="valid_from" class="block mt-1 w-full" type="date" name="valid_from" :value="old('valid_from', $nightPrice->valid_from->format('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('valid_from')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="valid_to" :value="__('Valid To (Optional)')" />
                                <x-text-input id="valid_to" class="block mt-1 w-full" type="date" name="valid_to" :value="old('valid_to', $nightPrice->valid_to ? $nightPrice->valid_to->format('Y-m-d') : '')" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Leave empty for no end date</p>
                                <x-input-error :messages="$errors->get('valid_to')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="active" {{ old('status', $nightPrice->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $nightPrice->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.hotels.night-prices.index', $hotel) }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100 mr-4">Cancel</a>
                            <x-primary-button>
                                {{ __('Update Night Price') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

