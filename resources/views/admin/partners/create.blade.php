@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold">{{ __('create_partner') }}</h1>
    </div>

    <div class="mb-4">
        <a href="{{ route('admin.partners.index') }}" class="text-logo-link hover:underline inline-flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            {{ __('back_to_partners') }}
        </a>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6">
            <form method="POST" action="{{ route('admin.partners.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <x-input-label for="name" :value="__('name')" />
                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="logo" :value="__('logo')" />
                    <x-text-input id="logo" class="block mt-1 w-full" type="file" name="logo" accept="image/jpeg,image/png,image/jpg" required />
                    <p class="mt-1 text-sm text-gray-500">{{ __('logo_hint') }}</p>
                    <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="url" :value="__('url')" />
                    <x-text-input id="url" class="block mt-1 w-full" type="url" name="url" :value="old('url')" placeholder="https://example.com" />
                    <p class="mt-1 text-sm text-gray-500">{{ __('url_optional') }}</p>
                    <x-input-error :messages="$errors->get('url')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="sort_order" :value="__('sort_order')" />
                    <x-text-input id="sort_order" class="block mt-1 w-full" type="number" name="sort_order" :value="old('sort_order', 0)" min="0" />
                    <p class="mt-1 text-sm text-gray-500">{{ __('sort_order_hint') }}</p>
                    <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="active" value="1" {{ old('active', true) ? 'checked' : '' }} class="rounded border-gray-300">
                        <span class="ml-2 text-sm text-gray-600">{{ __('active') }}</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <a href="{{ route('admin.partners.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">{{ __('cancel') }}</a>
                    <x-primary-button class="btn-logo-primary">
                        {{ __('create') }}
                    </x-primary-button>
                </div>
            </form>
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>
@endsection
