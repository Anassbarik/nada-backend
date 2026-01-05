<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('edit_partner') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('admin.partners.index') }}" 
                   class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 inline-flex items-center"
                   data-livewire-ignore="true">
                    ← {{ __('back_to_partners') }}
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.partners.update', $partner) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <x-input-label for="name" :value="__('name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $partner->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="logo" :value="__('logo')" />
                            @if($partner->logo_path)
                                <div class="mb-2">
                                    <img src="{{ $partner->logo_url }}" alt="{{ $partner->name }}" class="h-16 w-auto object-contain border border-gray-300 dark:border-gray-700 rounded p-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('current_logo') }}</p>
                                </div>
                            @endif
                            <x-text-input id="logo" class="block mt-1 w-full" type="file" name="logo" accept="image/jpeg,image/png,image/jpg" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('logo_hint_update') }}</p>
                            <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="url" :value="__('url')" />
                            <x-text-input id="url" class="block mt-1 w-full" type="url" name="url" :value="old('url', $partner->url)" placeholder="https://example.com" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('url_optional') }}</p>
                            <x-input-error :messages="$errors->get('url')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="sort_order" :value="__('sort_order')" />
                            <x-text-input id="sort_order" class="block mt-1 w-full" type="number" name="sort_order" :value="old('sort_order', $partner->sort_order)" min="0" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('sort_order_hint') }}</p>
                            <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="active" value="1" {{ old('active', $partner->active) ? 'checked' : '' }} class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800">
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ __('active') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.partners.index') }}" 
                               class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100 mr-4"
                               data-livewire-ignore="true">{{ __('cancel') }}</a>
                            <x-primary-button>
                                {{ __('update') }}
                            </x-primary-button>
                        </div>
                    </form>

                    <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                        <form method="POST" action="{{ route('admin.partners.destroy', $partner) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this partner?') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('delete_partner') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

