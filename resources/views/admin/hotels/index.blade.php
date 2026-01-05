<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Hotels for') }}: {{ $event->name }}
            </h2>
            <a href="{{ route('admin.events.hotels.create', $event) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ __('add_hotel') }}
            </a>
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
                <a href="{{ route('admin.events.index') }}" 
                   class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 inline-flex items-center"
                   data-livewire-ignore="true">
                    ← {{ __('back_to_events') }}
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('name') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('location') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('pricing') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('status') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($hotels as $hotel)
                                    <tr onclick="window.location='{{ route('admin.hotels.images.index', $hotel) }}'" class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $hotel->name }}
                                            @if($hotel->rating)
                                                @php $stars = $hotel->rating_stars @endphp
                                                <div class="mt-1 flex items-center text-xs">
                                                    <span class="text-yellow-400">
                                                        {{ str_repeat('⭐', $stars['full']) }}
                                                        @if($stars['half'])⭐@endif
                                                        {{ str_repeat('☆', $stars['empty']) }}
                                                    </span>
                                                    <span class="ml-1 text-gray-600 dark:text-gray-400">{{ $stars['text'] }}</span>
                                                    @if($hotel->review_count)
                                                        <span class="ml-1 text-gray-500 dark:text-gray-400">({{ $hotel->review_count }})</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            @if($hotel->location_url)
                                                <a href="{{ $hotel->location_url }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                    {{ $hotel->location }} 📍
                                                </a>
                                            @else
                                                {{ $hotel->location }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            @if($hotel->packages->where('disponibilite', true)->count() > 0)
                                                <span class="text-blue-600 dark:text-blue-400">{{ $hotel->packages->where('disponibilite', true)->count() }} package(s) disponible(s)</span>
                                            @else
                                                <span class="text-gray-400">Aucun package</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $hotel->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ ucfirst($hotel->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" onclick="event.stopPropagation();">
                                            <a href="{{ route('admin.hotels.packages.index', $hotel) }}" 
                                               class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-2" 
                                               title="{{ __('packages') }}"
                                               data-livewire-ignore="true">📦</a>
                                            <a href="{{ route('admin.hotels.edit', $hotel) }}" 
                                               class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3"
                                               data-livewire-ignore="true">{{ __('edit') }}</a>
                                            <form method="POST" action="{{ route('admin.hotels.destroy', $hotel) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this hotel?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">{{ __('delete') }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('no_hotels') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $hotels->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

