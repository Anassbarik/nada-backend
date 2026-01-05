<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Event') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <x-input-label for="name" :value="__('name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $event->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="venue" :value="__('venue')" />
                            <x-text-input id="venue" class="block mt-1 w-full" type="text" name="venue" :value="old('venue', $event->venue)" placeholder="e.g., Dakhla" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('venue_hint') }}</p>
                            <x-input-error :messages="$errors->get('venue')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="start_date" :value="__('start_date')" />
                                <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date', $event->start_date?->format('Y-m-d'))" />
                                <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="end_date" :value="__('end_date')" />
                                <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date', $event->end_date?->format('Y-m-d'))" />
                                <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="website_url" :value="__('website_url')" />
                            <x-text-input id="website_url" class="block mt-1 w-full" type="url" name="website_url" :value="old('website_url', $event->website_url)" placeholder="https://example.com" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('website_url_optional') }}</p>
                            <x-input-error :messages="$errors->get('website_url')" class="mt-2" />
                        </div>

                        @if($event->organizer_logo)
                            <div class="mb-4">
                                <x-input-label :value="__('current_organizer_logo')" />
                                <img src="{{ $event->organizer_logo_url }}" alt="Organizer Logo" class="mt-2 h-16 w-auto object-contain rounded">
                            </div>
                        @endif

                        <div class="mb-4">
                            <x-input-label for="organizer_logo" :value="__('organizer_logo')" />
                            <x-text-input id="organizer_logo" class="block mt-1 w-full" type="file" name="organizer_logo" accept="image/jpeg,image/png,image/jpg" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('organizer_logo_hint_update') }}</p>
                            <x-input-error :messages="$errors->get('organizer_logo')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description" :value="__('description')" />
                            <textarea id="description" name="description" rows="4" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $event->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        @if($event->logo_path)
                            <div class="mb-4">
                                <x-input-label :value="__('Current Logo')" />
                                <img src="{{ $event->logo_url }}" alt="Logo" class="mt-2 h-20 w-20 object-cover rounded">
                            </div>
                        @endif

                        <div class="mb-4">
                            <x-input-label for="logo" :value="__('Logo')" />
                            <x-text-input id="logo" class="block mt-1 w-full" type="file" name="logo" accept="image/*" />
                            <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                        </div>

                        @if($event->banner_path)
                            <div class="mb-4">
                                <x-input-label :value="__('Current Banner')" />
                                <img src="{{ $event->banner_url }}" alt="Banner" class="mt-2 h-32 w-full object-cover rounded">
                            </div>
                        @endif

                        <div class="mb-4">
                            <x-input-label for="banner" :value="__('Banner')" />
                            <x-text-input id="banner" class="block mt-1 w-full" type="file" name="banner" accept="image/*" />
                            <x-input-error :messages="$errors->get('banner')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <div class="flex items-center justify-between">
                                <x-input-label :value="__('Content Pages')" />
                                <a href="{{ route('admin.events.content.index', $event) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 text-sm">
                                    Manage Content Pages →
                                </a>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage Conditions, Informations, and FAQ pages separately.</p>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="draft" {{ old('status', $event->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status', $event->status) === 'published' ? 'selected' : '' }}>Published</option>
                                <option value="archived" {{ old('status', $event->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.events.index') }}" 
                               class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100 mr-4"
                               data-livewire-ignore="true">{{ __('cancel') }}</a>
                            <x-primary-button>
                                {{ __('Update Event') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

