<div>
    <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $page_type === 'conditions' ? 'Conditions de Réservation' : ($page_type === 'informations' ? 'Informations Générales' : 'FAQ') }}</h3>
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Hero Image (Grande image en haut de page)
            </label>
            @if($hero_image)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $hero_image) }}" alt="Hero" class="h-48 w-full object-cover rounded-lg">
                    <p class="text-sm text-gray-500 mt-1">Image actuelle</p>
                </div>
            @endif
            <input type="file" 
                   wire:model="uploadedHeroImage" 
                   accept="image/*" 
                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            @error('uploadedHeroImage') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="mb-6">
        <div class="flex justify-between items-center mb-4">
            <h4 class="text-md font-medium text-gray-900">Sections (Glisser pour réorganiser)</h4>
            <button type="button" 
                    wire:click="addSection" 
                    class="btn-logo-primary text-white font-bold py-2 px-4 rounded text-sm">
                + Ajouter Section
            </button>
        </div>

        @if(count($sections) > 0)
            <div class="space-y-4">
                @foreach($sections as $index => $section)
                    <div class="border rounded-lg p-4 bg-gray-50" wire:key="section-{{ $index }}">
                        <div class="flex items-start justify-between mb-2">
                            <span class="text-sm font-medium text-gray-500">Section {{ $index + 1 }}</span>
                            <button type="button" 
                                    wire:click="removeSection({{ $index }})"
                                    class="text-red-600 hover:text-red-900 text-sm">
                                Supprimer
                            </button>
                        </div>
                        
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
                            <input type="text" 
                                   wire:model="sections.{{ $index }}.title"
                                   class="block w-full border-gray-300 rounded-md shadow-sm"
                                   placeholder="Ex: Conditions Générales">
                            @error("sections.{$index}.title") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contenu</label>
                            <textarea wire:model="sections.{{ $index }}.content"
                                      rows="4"
                                      class="block w-full border-gray-300 rounded-md shadow-sm"
                                      placeholder="Contenu de la section..."></textarea>
                            @error("sections.{$index}.content") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                <p class="text-gray-500 mb-4">Aucune section créée.</p>
                <button type="button" 
                        wire:click="addSection" 
                        class="btn-logo-primary text-white font-bold py-2 px-4 rounded">
                    + Ajouter Première Section
                </button>
            </div>
        @endif
    </div>

    <div class="flex items-center justify-end mt-6">
        <button type="button" 
                wire:click="save" 
                class="btn-logo-primary text-white font-bold py-2 px-4 rounded">
            Sauvegarder
        </button>
    </div>

    @if (session()->has('success'))
        <x-alert type="success" class="mt-4">
            {{ session('success') }}
        </x-alert>
    @endif
</div>
