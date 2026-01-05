<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Modifier Package') }} - {{ $hotel->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('admin.hotels.packages.index', $hotel) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">← Retour aux Packages</a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.hotels.packages.update', [$hotel, $package]) }}" x-data="{ 
                        prix_ht: {{ old('prix_ht', $package->prix_ht) }},
                        prix_ttc: {{ old('prix_ttc', $package->prix_ttc) }},
                        chambres_restantes: {{ old('chambres_restantes', $package->chambres_restantes) }},
                        disponibilite: {{ $package->disponibilite ? 'true' : 'false' }},
                        updateTTC() { this.prix_ttc = this.prix_ht * 1.20; },
                        updateDisponibilite() { this.disponibilite = this.chambres_restantes > 0; }
                    }">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="nom_package" :value="__('Nom de Package')" />
                                <x-text-input id="nom_package" class="block mt-1 w-full" type="text" name="nom_package" :value="old('nom_package', $package->nom_package)" required autofocus />
                                <x-input-error :messages="$errors->get('nom_package')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="type_chambre" :value="__('Type de chambre')" />
                                <x-text-input id="type_chambre" class="block mt-1 w-full" type="text" name="type_chambre" :value="old('type_chambre', $package->type_chambre)" required />
                                <x-input-error :messages="$errors->get('type_chambre')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="check_in" :value="__('Check in')" />
                                <x-text-input id="check_in" class="block mt-1 w-full" type="date" name="check_in" :value="old('check_in', $package->check_in->format('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('check_in')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="check_out" :value="__('Check out')" />
                                <x-text-input id="check_out" class="block mt-1 w-full" type="date" name="check_out" :value="old('check_out', $package->check_out->format('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('check_out')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="occupants" :value="__('Occupants')" />
                                <x-text-input id="occupants" class="block mt-1 w-full" type="number" name="occupants" :value="old('occupants', $package->occupants)" min="1" required />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">(min 1)</p>
                                <x-input-error :messages="$errors->get('occupants')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="prix_ht" :value="__('Prix Hors tax (MAD)')" />
                                <x-text-input id="prix_ht" class="block mt-1 w-full" type="number" step="0.01" name="prix_ht" :value="old('prix_ht', $package->prix_ht)" min="0" required x-model="prix_ht" @input="updateTTC()" />
                                <x-input-error :messages="$errors->get('prix_ht')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="prix_ttc" :value="__('Prix TTC (MAD)')" />
                                <x-text-input id="prix_ttc" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700" type="number" step="0.01" name="prix_ttc" x-model="prix_ttc" readonly />
                                <p class="mt-1 text-sm text-green-600 dark:text-green-400">✅ Auto-calculé (+20% TVA)</p>
                            </div>

                            <div>
                                <x-input-label for="quantite_chambres" :value="__('Quantité de chambres')" />
                                <x-text-input id="quantite_chambres" class="block mt-1 w-full" type="number" name="quantite_chambres" :value="old('quantite_chambres', $package->quantite_chambres)" min="1" required />
                                <x-input-error :messages="$errors->get('quantite_chambres')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="chambres_restantes" :value="__('Nombre chambres restant')" />
                                <x-text-input id="chambres_restantes" class="block mt-1 w-full" type="number" name="chambres_restantes" :value="old('chambres_restantes', $package->chambres_restantes)" min="0" required x-model="chambres_restantes" @input="updateDisponibilite()" />
                                <x-input-error :messages="$errors->get('chambres_restantes')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="disponibilite" :value="__('Disponibilité')" />
                                <div class="mt-2">
                                    <span class="px-3 py-2 inline-flex text-sm leading-5 font-semibold rounded-full" 
                                          :class="disponibilite ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'">
                                        <span x-text="disponibilite ? '✅ Oui' : '❌ Non'"></span>
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Auto-calculé</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.hotels.packages.index', $hotel) }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100 mr-4">Annuler</a>
                            <x-primary-button>
                                {{ __('Mettre à jour Package') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
