@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">{{ __('add_hotel') }} - {{ $event->name }}</h1>
    </div>

    <div class="mb-4">
        <a href="{{ route('admin.events.hotels.index', $event) }}" 
           class="text-logo-link hover:underline inline-flex items-center"
           data-livewire-ignore="true">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            {{ __('back_to_hotels') }}
        </a>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6">
                    <form method="POST" action="{{ route('admin.events.hotels.store', $event) }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="name" :value="__('name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="stars" :value="__('stars')" />
                            <select id="stars" name="stars" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="" disabled {{ old('stars') ? '' : 'selected' }}>{{ __('Select') }}</option>
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" {{ (string) old('stars') === (string) $i ? 'selected' : '' }}>
                                        {{ $i }} ★
                                    </option>
                                @endfor
                            </select>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Hotel category (étoiles)') }}</p>
                            <x-input-error :messages="$errors->get('stars')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="location" :value="__('location')" />
                            <x-text-input id="location" class="block mt-1 w-full" type="text" name="location" :value="old('location')" required />
                            <x-input-error :messages="$errors->get('location')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="location_url" :value="__('location_url')" />
                            <x-text-input id="location_url" class="block mt-1 w-full" type="url" name="location_url" :value="old('location_url')" placeholder="https://maps.app.goo.gl/..." />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Google Maps URL (optional)') }}</p>
                            <x-input-error :messages="$errors->get('location_url')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="duration" :value="__('Duration')" />
                            <x-text-input id="duration" class="block mt-1 w-full" type="text" name="duration" :value="old('duration')" placeholder="e.g., 15 min, 30 minutes, 1 hour" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Duration from hotel to event venue (optional)') }}</p>
                            <x-input-error :messages="$errors->get('duration')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="rating" :value="__('rating')" />
                            <x-text-input id="rating" class="block mt-1 w-full" type="number" name="rating" :value="old('rating')" step="0.1" min="0" max="5" placeholder="4.5" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Rating from 0 to 5 (optional)') }}</p>
                            <x-input-error :messages="$errors->get('rating')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="review_count" :value="__('review_count')" />
                            <x-text-input id="review_count" class="block mt-1 w-full" type="number" name="review_count" :value="old('review_count')" min="0" placeholder="127" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Number of reviews (optional)') }}</p>
                            <x-input-error :messages="$errors->get('review_count')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description" :value="__('description')" />
                            <textarea id="description" name="description" rows="4" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="inclusions" :value="__('Inclusions')" />
                            <div x-data="{ 
                                inclusions: {{ json_encode(old('inclusions', [''])) }},
                                addInclusion() { this.inclusions.push(''); },
                                removeInclusion(index) { this.inclusions.splice(index, 1); }
                            }" class="space-y-2">
                                <template x-for="(inclusion, index) in inclusions" :key="index">
                                    <div class="flex gap-2">
                                        <input 
                                            type="text" 
                                            :name="'inclusions[' + index + ']'" 
                                            x-model="inclusions[index]" 
                                            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" 
                                            :placeholder="'{{ __('Enter inclusion item') }}'"
                                        />
                                        <button 
                                            type="button" 
                                            @click="removeInclusion(index)" 
                                            class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600"
                                            x-show="inclusions.length > 1"
                                        >
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </template>
                                <button 
                                    type="button" 
                                    @click="addInclusion()" 
                                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 inline-flex items-center"
                                >
                                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                                    {{ __('Add Inclusion') }}
                                </button>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('List what is included in all packages for this hotel (e.g., Breakfast, Transfer, etc.)') }}</p>
                            <x-input-error :messages="$errors->get('inclusions.*')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="website" :value="__('website')" />
                            <x-text-input id="website" class="block mt-1 w-full" type="url" name="website" :value="old('website')" placeholder="https://example.com" />
                            <x-input-error :messages="$errors->get('website')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">You can upload images after creating the hotel.</p>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="status" :value="__('status')" />
                            <select id="status" name="status" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>{{ __('active') }}</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>{{ __('inactive') }}</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.events.hotels.index', $event) }}" 
                               class="text-gray-600 hover:text-gray-900 mr-4"
                               data-livewire-ignore="true">{{ __('cancel') }}</a>
                            <x-primary-button class="btn-logo-primary">
                                {{ __('create') }}
                            </x-primary-button>
                        </div>
                    </form>
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>
@endsection

