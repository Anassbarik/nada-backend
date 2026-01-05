@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold">{{ __('edit_hotel') }}</h1>
    </div>

    <div class="mb-4">
        <a href="{{ route('admin.events.hotels.index', $hotel->event) }}" 
           class="text-logo-link hover:underline inline-flex items-center"
           data-livewire-ignore="true">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            {{ __('back_to_hotels') }}
        </a>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6">
                    <form method="POST" action="{{ route('admin.hotels.update', $hotel) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <x-input-label for="name" :value="__('name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $hotel->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="location" :value="__('location')" />
                            <x-text-input id="location" class="block mt-1 w-full" type="text" name="location" :value="old('location', $hotel->location)" required />
                            <x-input-error :messages="$errors->get('location')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="location_url" :value="__('location_url')" />
                            <x-text-input id="location_url" class="block mt-1 w-full" type="url" name="location_url" :value="old('location_url', $hotel->location_url)" placeholder="https://maps.app.goo.gl/..." />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Google Maps URL (optional)') }}</p>
                            <x-input-error :messages="$errors->get('location_url')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="rating" :value="__('rating')" />
                            <x-text-input id="rating" class="block mt-1 w-full" type="number" name="rating" :value="old('rating', $hotel->rating)" step="0.1" min="0" max="5" placeholder="4.5" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Rating from 0 to 5 (optional)') }}</p>
                            @if($hotel->rating)
                                @php $stars = $hotel->rating_stars @endphp
                                <div class="mt-2 flex items-center">
                                    <span class="text-yellow-400">
                                        {{ str_repeat('★', $stars['full']) }}
                                        @if($stars['half'])★@endif
                                        {{ str_repeat('☆', $stars['empty']) }}
                                    </span>
                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ $stars['text'] }}</span>
                                    @if($hotel->review_count)
                                        <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">({{ $hotel->review_count }} {{ __('reviews') }})</span>
                                    @endif
                                </div>
                            @endif
                            <x-input-error :messages="$errors->get('rating')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="review_count" :value="__('review_count')" />
                            <x-text-input id="review_count" class="block mt-1 w-full" type="number" name="review_count" :value="old('review_count', $hotel->review_count)" min="0" placeholder="127" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Number of reviews (optional)') }}</p>
                            <x-input-error :messages="$errors->get('review_count')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description" :value="__('description')" />
                            <textarea id="description" name="description" rows="4" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $hotel->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="website" :value="__('website')" />
                            <x-text-input id="website" class="block mt-1 w-full" type="url" name="website" :value="old('website', $hotel->website)" placeholder="https://example.com" />
                            <x-input-error :messages="$errors->get('website')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <div class="flex items-center justify-between">
                                <x-input-label :value="__('hotel_images')" />
                                <a href="{{ route('admin.hotels.images.index', $hotel) }}" class="text-logo-link hover:underline text-sm">
                                    {{ __('manage_images') }} ({{ $hotel->images->count() }}/10) →
                                </a>
                            </div>
                            @if($hotel->images->count() > 0)
                                <div class="mt-2 grid grid-cols-4 gap-2">
                                    @foreach($hotel->images->take(4) as $image)
                                        <img src="{{ $image->url }}" alt="{{ $image->alt_text ?? 'Hotel image' }}" class="h-20 w-full object-cover rounded">
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No images uploaded yet.</p>
                            @endif
                        </div>

                        <div class="mb-4">
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="active" {{ old('status', $hotel->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $hotel->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.events.hotels.index', $hotel->event) }}" 
                               class="text-gray-600 hover:text-gray-900 mr-4"
                               data-livewire-ignore="true">{{ __('cancel') }}</a>
                            <x-primary-button class="btn-logo-primary">
                                {{ __('update') }}
                            </x-primary-button>
                        </div>
                    </form>
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>
@endsection

