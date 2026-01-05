<div>
    {{-- Success/Error Messages --}}
    @if (session()->has('message'))
        <x-alert type="success" class="mb-6">
            {{ session('message') }}
        </x-alert>
    @endif

    {{-- Toggle Create Form --}}
    <div class="mb-8">
        <button 
            wire:click="toggleForm"
            class="btn-logo-primary px-6 py-2 text-white rounded-lg transition-colors">
            {{ $showForm ? __('cancel') : __('new_package') }}
        </button>
    </div>

    {{-- Create Form --}}
    @if($showForm)
        <x-shadcn.card class="shadow-lg mb-8">
            <x-shadcn.card-content class="p-8">
                <h3 class="text-xl font-semibold mb-6">{{ __('new_package') }}</h3>
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
                        <x-text-input id="prix_ttc" class="block mt-1 w-full bg-gray-100" type="text" value="{{ number_format($prix_ttc, 2) }} MAD" readonly />
                        <p class="mt-1 text-sm text-green-600">{{ __('auto_calculated') }} (+20% {{ __('vat') }})</p>
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
                        <span class="text-gray-700">{{ __('disponibilite') }} ({{ $chambres_restantes }} {{ __('rooms_available') }})</span>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-4 mt-8">
                    <button type="button" wire:click="toggleForm" class="px-8 py-3 bg-gray-200 text-gray-800 rounded-xl hover:bg-gray-300 transition-colors">
                        {{ __('cancel') }}
                    </button>
                    <button type="submit" class="btn-logo-primary px-10 py-3 text-white rounded-xl shadow-lg transition-colors">
                        {{ __('create_package') }}
                    </button>
                </div>
            </form>
            </x-shadcn.card-content>
        </x-shadcn.card>
    @endif

    {{-- Packages Table --}}
    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-header>
            <x-shadcn.card-title>{{ $hotel->name }} ({{ $packages->total() }} {{ __('packages') }})</x-shadcn.card-title>
        </x-shadcn.card-header>
        <x-shadcn.card-content class="p-0">
            <x-shadcn.table responsive>
                <x-shadcn.table-header>
                    <x-shadcn.table-row>
                        <x-shadcn.table-head>{{ __('nom_package') }}</x-shadcn.table-head>
                        <x-shadcn.table-head>{{ __('type_chambre') }}</x-shadcn.table-head>
                        <x-shadcn.table-head>{{ __('occupants') }}</x-shadcn.table-head>
                        <x-shadcn.table-head>{{ __('prix_ttc') }}</x-shadcn.table-head>
                        <x-shadcn.table-head>{{ __('chambres_restantes') }}</x-shadcn.table-head>
                        <x-shadcn.table-head>{{ __('disponibilite') }}</x-shadcn.table-head>
                        <x-shadcn.table-head>{{ __('Actions') }}</x-shadcn.table-head>
                    </x-shadcn.table-row>
                </x-shadcn.table-header>
                <x-shadcn.table-body>
                    @forelse($packages as $package)
                        <x-shadcn.table-row hover>
                            <x-shadcn.table-cell class="font-medium">{{ $package->nom_package }}</x-shadcn.table-cell>
                            <x-shadcn.table-cell>{{ $package->type_chambre }}</x-shadcn.table-cell>
                            <x-shadcn.table-cell>{{ $package->occupants }}</x-shadcn.table-cell>
                            <x-shadcn.table-cell class="font-semibold text-green-600">{{ number_format($package->prix_ttc, 2) }} MAD</x-shadcn.table-cell>
                            <x-shadcn.table-cell>
                                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-sm">
                                    {{ $package->chambres_restantes }} / {{ $package->quantite_chambres }}
                                </span>
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell>
                                <x-shadcn.badge variant="{{ $package->disponibilite ? 'default' : 'destructive' }}">
                                    {{ $package->disponibilite ? __('yes') : __('no') }}
                                </x-shadcn.badge>
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell class="space-x-2">
                                <a href="{{ route('admin.hotels.packages.edit', [$hotel, $package]) }}" 
                                   class="text-logo-link hover:underline"
                                   data-livewire-ignore="true">{{ __('edit') }}</a>
                                <button wire:click="deletePackage({{ $package->id }})" 
                                        class="text-red-600 hover:underline"
                                        onclick="return confirm('{{ __('Are you sure you want to delete this package?') }}')">
                                    {{ __('delete') }}
                                </button>
                            </x-shadcn.table-cell>
                        </x-shadcn.table-row>
                    @empty
                        <x-shadcn.table-row>
                            <x-shadcn.table-cell colspan="7" class="text-center text-muted-foreground">
                                {{ __('no_packages') }}
                            </x-shadcn.table-cell>
                        </x-shadcn.table-row>
                    @endforelse
                </x-shadcn.table-body>
            </x-shadcn.table>
        </x-shadcn.card-content>
        @if($packages->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $packages->links() }}
            </div>
        @endif
    </x-shadcn.card>
</div>
