@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold">{{ __('Create Event') }}</h1>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6">
                    <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="name" :value="__('name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="venue" :value="__('venue')" />
                            <x-text-input id="venue" class="block mt-1 w-full" type="text" name="venue" :value="old('venue')" placeholder="e.g., Dakhla" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('venue_hint') }}</p>
                            <x-input-error :messages="$errors->get('venue')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="start_date" :value="__('start_date')" />
                                <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date')" />
                                <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="end_date" :value="__('end_date')" />
                                <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date')" />
                                <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="website_url" :value="__('website_url')" />
                            <x-text-input id="website_url" class="block mt-1 w-full" type="url" name="website_url" :value="old('website_url')" placeholder="https://example.com" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('website_url_optional') }}</p>
                            <x-input-error :messages="$errors->get('website_url')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="organizer_logo" :value="__('organizer_logo')" />
                            <x-text-input id="organizer_logo" class="block mt-1 w-full" type="file" name="organizer_logo" accept="image/jpeg,image/png,image/jpg" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('organizer_logo_hint') }}</p>
                            <x-input-error :messages="$errors->get('organizer_logo')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description" :value="__('description')" />
                            <textarea id="description" name="description" rows="4" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="logo" :value="__('logo')" />
                            <x-text-input id="logo" class="block mt-1 w-full" type="file" name="logo" accept="image/*" />
                            <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="banner" :value="__('banner')" />
                            <x-text-input id="banner" class="block mt-1 w-full" type="file" name="banner" accept="image/*" />
                            <x-input-error :messages="$errors->get('banner')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="status" :value="__('status')" />
                            <select id="status" name="status" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>{{ __('draft') }}</option>
                                <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>{{ __('published') }}</option>
                                <option value="archived" {{ old('status') === 'archived' ? 'selected' : '' }}>{{ __('archived') }}</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.events.index') }}" 
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

