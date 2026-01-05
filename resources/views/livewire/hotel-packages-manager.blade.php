<div>
    {{-- Success/Error Messages --}}
    @if (session()->has('message'))
        <div class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-lg dark:bg-green-900 dark:border-green-600 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    {{-- Toggle Create Form --}}
    <div class="mb-8">
        <button 
            wire:click="toggleForm"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors">
            {{ $showForm ? __('cancel') : '➕ ' . __('new_package') }}
        </button>
    </div>

    {{-- Create Form --}}
    @if($showForm)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 mb-8">
            <h3 class="text-xl font-semibold mb-6 text-gray-900 dark:text-gray-100">{{ __('new_package') }}</h3>
            <form wire:submit="createPackage">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <x-input-label for="nom_package" :value="__('nom_package')" />
                        <x-text-input id="nom_package" class="block mt-1 w-full" type="text" wire:model.live="nom_package" required autofocus />
                        <x-input-error :messages="$errors->get('nom_package')" class="mt-2" />
                    </div>
                    
                    <div>
                        <x-input-label for="type_chambre" :value="__('type_chambre')" />
                        <x-text-input id="type_chambre" class="block mt-1 w-full" type="text" wire:model.live="type_chambre" required />
                        <x-input-error :messages="$errors->get('type_chambre')" class="mt-2" />
                    </div>
                    
                    <div>
                        <x-input-label for="check_in" :value="__('check_in')" />
                        <x-text-input id="check_in" class="block mt-1 w-full" type="date" wire:model.live="check_in" required />
                        <x-input-error :messages="$errors->get('check_in')" class="mt-2" />
                    </div>
                    
                    <div>
                        <x-input-label for="check_out" :value="__('check_out')" />
                        <x-text-input id="check_out" class="block mt-1 w-full" type="date" wire:model.live="check_out" required />
                        <x-input-error :messages="$errors->get('check_out')" class="mt-2" />
                    </div>
                    
                    <div>
                        <x-input-label for="occupants" :value="__('occupants')" />
                        <x-text-input id="occupants" class="block mt-1 w-full" type="number" min="1" wire:model.live="occupants" required />
                        <x-input-error :messages="$errors->get('occupants')" class="mt-2" />
                    </div>
                    
                    <div>
                        <x-input-label for="prix_ht" :value="__('prix_ht')" />
                        <x-text-input id="prix_ht" class="block mt-1 w-full" type="number" step="0.01" wire:model.live="prix_ht" min="0" required />
                        <x-input-error :messages="$errors->get('prix_ht')" class="mt-2" />
                    </div>
                    
                    <div class="lg:col-span-2">
                        <x-input-label for="prix_ttc" :value="__('prix_ttc')" />
                        <x-text-input id="prix_ttc" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700" type="text" value="{{ number_format($prix_ttc, 2) }} MAD" readonly />
                        <p class="mt-1 text-sm text-green-600 dark:text-green-400">✅ {{ __('auto_calculated') }} (+20% {{ __('vat') }})</p>
                    </div>
                    
                    <div>
                        <x-input-label for="quantite_chambres" :value="__('quantite_chambres')" />
                        <x-text-input id="quantite_chambres" class="block mt-1 w-full" type="number" min="1" wire:model.live="quantite_chambres" required />
                        <x-input-error :messages="$errors->get('quantite_chambres')" class="mt-2" />
                    </div>
                    
                    <div>
                        <x-input-label for="chambres_restantes" :value="__('chambres_restantes')" />
                        <x-text-input id="chambres_restantes" class="block mt-1 w-full" type="number" min="0" wire:model.live="chambres_restantes" required />
                        <x-input-error :messages="$errors->get('chambres_restantes')" class="mt-2" />
                    </div>
                    
                    <div class="lg:col-span-3 flex items-center space-x-3">
                        <input type="checkbox" {{ $disponibilite ? 'checked' : '' }} disabled class="rounded" />
                        <span class="text-gray-700 dark:text-gray-300">{{ __('disponibilite') }} ({{ $chambres_restantes }} {{ __('rooms_available') }})</span>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-4 mt-8">
                    <button type="button" wire:click="toggleForm" class="px-8 py-3 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                        {{ __('cancel') }}
                    </button>
                    <button type="submit" class="px-10 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 shadow-lg transition-colors">
                        {{ __('create_package') }}
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Packages Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $hotel->name }} ({{ $packages->total() }} {{ __('packages') }})</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('nom_package') }}</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('type_chambre') }}</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('occupants') }}</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('prix_ttc') }}</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('chambres_restantes') }}</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('disponibilite') }}</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($packages as $package)
                        <tr class="border-t hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">{{ $package->nom_package }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $package->type_chambre }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $package->occupants }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-green-600 dark:text-green-400">{{ number_format($package->prix_ttc, 2) }} MAD</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-sm">
                                    {{ $package->chambres_restantes }} / {{ $package->quantite_chambres }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $package->disponibilite ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                    {{ $package->disponibilite ? __('yes') : __('no') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium">
                                <a href="{{ route('admin.hotels.packages.edit', [$hotel, $package]) }}" 
                                   class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3"
                                   data-livewire-ignore="true">{{ __('edit') }}</a>
                                <button wire:click="deletePackage({{ $package->id }})" 
                                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                        onclick="return confirm('{{ __('Are you sure you want to delete this package?') }}')">
                                    {{ __('delete') }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                {{ __('no_packages') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($packages->hasPages())
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                {{ $packages->links() }}
            </div>
        @endif
    </div>
</div>
