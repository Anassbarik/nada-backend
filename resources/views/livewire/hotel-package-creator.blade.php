<div>
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg dark:bg-green-900 dark:border-green-600 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    
    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('new_package') }}</h3>
    
    <form wire:submit="save">
        <div class="grid grid-cols-2 gap-4">
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
                <x-text-input id="occupants" class="block mt-1 w-full" type="number" wire:model.live="occupants" min="1" required />
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">(min 1)</p>
                <x-input-error :messages="$errors->get('occupants')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="prix_ht" :value="__('prix_ht')" />
                <x-text-input id="prix_ht" class="block mt-1 w-full" type="number" step="0.01" wire:model.live="prix_ht" min="0" required />
                <x-input-error :messages="$errors->get('prix_ht')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="prix_ttc" :value="__('prix_ttc')" />
                <x-text-input id="prix_ttc" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700" type="number" step="0.01" wire:model="prix_ttc" readonly />
                <p class="mt-1 text-sm text-green-600 dark:text-green-400">✅ {{ __('auto_calculated') }} (+20% {{ __('vat') }})</p>
            </div>

            <div>
                <x-input-label for="quantite_chambres" :value="__('quantite_chambres')" />
                <x-text-input id="quantite_chambres" class="block mt-1 w-full" type="number" wire:model.live="quantite_chambres" min="1" required />
                <x-input-error :messages="$errors->get('quantite_chambres')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="chambres_restantes" :value="__('chambres_restantes')" />
                <x-text-input id="chambres_restantes" class="block mt-1 w-full" type="number" wire:model.live="chambres_restantes" min="0" required />
                <x-input-error :messages="$errors->get('chambres_restantes')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="disponibilite" :value="__('disponibilite')" />
                <div class="mt-2">
                    <span class="px-3 py-2 inline-flex text-sm leading-5 font-semibold rounded-full {{ $disponibilite ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                        {{ $disponibilite ? '✅ ' . __('yes') : '❌ ' . __('no') }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('auto_calculated') }}</p>
            </div>
        </div>

        <div class="flex items-center justify-end mt-6 space-x-4">
            <button type="button" wire:click="resetForm" class="px-6 py-2 text-gray-700 bg-gray-200 dark:bg-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                {{ __('cancel') }}
            </button>
            <x-primary-button type="submit">
                {{ __('create_package') }}
            </x-primary-button>
        </div>
    </form>
</div>
