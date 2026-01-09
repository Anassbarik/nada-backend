@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold">{{ __('new_package') }} - {{ $hotel->name }}</h1>
    </div>

    <div class="mb-4">
        <a href="{{ route('admin.hotels.packages.index', $hotel) }}" 
           class="text-logo-link hover:underline inline-flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            {{ __('Back to Packages') }}
        </a>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-8">
            <form method="POST" action="{{ route('admin.hotels.packages.store', $hotel) }}" 
                  x-data="{ 
                      prix_ht: {{ old('prix_ht', 0) }},
                      prix_ttc: {{ old('prix_ttc', 0) }},
                      chambres_restantes: {{ old('chambres_restantes', 0) }},
                      disponibilite: false,
                      updateTTC() { this.prix_ttc = (this.prix_ht * 1.20).toFixed(2); },
                      updateDisponibilite() { this.disponibilite = this.chambres_restantes > 0; }
                  }">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <x-input-label for="nom_package" :value="__('nom_package')" />
                        <x-text-input id="nom_package" class="block mt-1 w-full" type="text" name="nom_package" value="{{ old('nom_package') }}" required autofocus />
                        <x-input-error :messages="$errors->get('nom_package')" class="mt-2" />
                    </div>
                    
                    <div>
                        <x-input-label for="type_chambre" :value="__('type_chambre')" />
                        <x-text-input id="type_chambre" class="block mt-1 w-full" type="text" name="type_chambre" value="{{ old('type_chambre') }}" required />
                        <x-input-error :messages="$errors->get('type_chambre')" class="mt-2" />
                    </div>
                    
                    <div>
                        <x-input-label for="check_in" :value="__('check_in')" />
                        <x-text-input id="check_in" class="block mt-1 w-full" type="date" name="check_in" value="{{ old('check_in') }}" required />
                        <x-input-error :messages="$errors->get('check_in')" class="mt-2" />
                    </div>
                    
                    <div>
                        <x-input-label for="check_out" :value="__('check_out')" />
                        <x-text-input id="check_out" class="block mt-1 w-full" type="date" name="check_out" value="{{ old('check_out') }}" required />
                        <x-input-error :messages="$errors->get('check_out')" class="mt-2" />
                    </div>
                    
                    <div>
                        <x-input-label for="occupants" :value="__('occupants')" />
                        <x-text-input id="occupants" class="block mt-1 w-full" type="number" min="1" name="occupants" value="{{ old('occupants', 1) }}" required />
                        <x-input-error :messages="$errors->get('occupants')" class="mt-2" />
                    </div>
                    
                    <div>
                        <x-input-label for="prix_ht" :value="__('prix_ht')" />
                        <x-text-input id="prix_ht" class="block mt-1 w-full" type="number" step="0.01" name="prix_ht" value="{{ old('prix_ht', 0) }}" min="0" required x-model="prix_ht" @input="updateTTC()" />
                        <x-input-error :messages="$errors->get('prix_ht')" class="mt-2" />
                    </div>
                    
                    <div class="lg:col-span-2">
                        <x-input-label for="prix_ttc" :value="__('prix_ttc')" />
                        <x-text-input id="prix_ttc" class="block mt-1 w-full bg-gray-100" type="text" x-model="prix_ttc" readonly />
                        <p class="mt-1 text-sm text-green-600">{{ __('auto_calculated') }} (+20% {{ __('vat') }})</p>
                    </div>
                    
                    <div>
                        <x-input-label for="quantite_chambres" :value="__('quantite_chambres')" />
                        <x-text-input id="quantite_chambres" class="block mt-1 w-full" type="number" min="1" name="quantite_chambres" value="{{ old('quantite_chambres', 1) }}" required />
                        <x-input-error :messages="$errors->get('quantite_chambres')" class="mt-2" />
                    </div>
                    
                    <div>
                        <x-input-label for="chambres_restantes" :value="__('chambres_restantes')" />
                        <x-text-input id="chambres_restantes" class="block mt-1 w-full" type="number" min="0" name="chambres_restantes" value="{{ old('chambres_restantes', 0) }}" required x-model="chambres_restantes" @input="updateDisponibilite()" />
                        <x-input-error :messages="$errors->get('chambres_restantes')" class="mt-2" />
                    </div>
                    
                    <div class="lg:col-span-3 flex items-center space-x-3">
                        <input type="checkbox" x-model="disponibilite" disabled class="rounded" />
                        <span class="text-gray-700">{{ __('disponibilite') }} (<span x-text="chambres_restantes"></span> {{ __('rooms_available') }})</span>
                    </div>

                </div>
                
                <div class="flex items-center justify-end mt-8">
                    <a href="{{ route('admin.hotels.packages.index', $hotel) }}" 
                       class="text-gray-600 hover:text-gray-900 mr-4">{{ __('cancel') }}</a>
                    <x-primary-button class="btn-logo-primary">
                        {{ __('create_package') }}
                    </x-primary-button>
                </div>
            </form>
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>
@endsection

