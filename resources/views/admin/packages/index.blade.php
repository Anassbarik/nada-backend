<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('packages_for') }}: {{ $hotel->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="mb-4">
                <a href="{{ route('admin.events.hotels.index', $hotel->event) }}" 
                   class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 inline-flex items-center"
                   data-livewire-ignore="true">
                    ← {{ __('back_to_hotels') }}
                </a>
            </div>

            {{-- Single Livewire Component handles both list and create --}}
            @livewire('hotel-packages-manager', ['hotel' => $hotel], key('packages-manager-' . $hotel->id))
        </div>
    </div>

</x-app-layout>
