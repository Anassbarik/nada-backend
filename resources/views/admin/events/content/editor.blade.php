@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold">{{ __('Éditer') }}: {{ $pageName }} - {{ $event->name }}</h1>
    </div>

    <div class="mb-4">
        <a href="{{ route('admin.events.content.index', $event) }}" class="text-logo-link hover:underline inline-flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Retour aux Pages de Contenu
        </a>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6">
            <form action="{{ route('admin.events.content.update', [$event, $pageType]) }}" method="POST" id="content-form">
                @csrf
                @method('PUT')

                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $pageName }}</h3>
                </div>

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-md font-medium text-gray-900">Sections</h4>
                        <button type="button" 
                                id="add-section-btn"
                                class="btn-logo-primary text-white font-bold py-2 px-4 rounded text-sm">
                            + Ajouter Section
                        </button>
                    </div>

                    <div id="sections-container" class="space-y-4">
                        @if($content && isset($content->sections) && count($content->sections) > 0)
                            @foreach($content->sections as $index => $section)
                                <div class="border rounded-lg p-4 bg-gray-50 section-item" data-index="{{ $index }}">
                                    <div class="flex items-start justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-500">Section {{ $index + 1 }}</span>
                                        <button type="button" 
                                                class="remove-section-btn text-red-600 hover:text-red-900 text-sm">
                                            Supprimer
                                        </button>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
                                        <input type="text" 
                                               name="sections[{{ $index }}][title]"
                                               value="{{ old("sections.{$index}.title", $section['title'] ?? '') }}"
                                               class="block w-full border-gray-300 rounded-md shadow-sm"
                                               placeholder="Ex: Conditions Générales"
                                               required>
                                        @error("sections.{$index}.title") 
                                            <span class="text-red-500 text-xs">{{ $message }}</span> 
                                        @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Contenu</label>
                                        <textarea name="sections[{{ $index }}][content]"
                                                  rows="4"
                                                  class="block w-full border-gray-300 rounded-md shadow-sm"
                                                  placeholder="Contenu de la section..."
                                                  required>{{ old("sections.{$index}.content", $section['content'] ?? '') }}</textarea>
                                        @error("sections.{$index}.content") 
                                            <span class="text-red-500 text-xs">{{ $message }}</span> 
                                        @enderror
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div id="empty-state" class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                                <p class="text-gray-500 mb-4">Aucune section créée.</p>
                                <button type="button" 
                                        id="add-first-section-btn"
                                        class="btn-logo-primary text-white font-bold py-2 px-4 rounded">
                                    + Ajouter Première Section
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-end mt-6">
                    <button type="submit" 
                            class="btn-logo-primary text-white font-bold py-2 px-4 rounded">
                        Sauvegarder
                    </button>
                </div>

                @if ($errors->any())
                    <div class="mt-4">
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </form>
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let sectionIndex = {{ ($content && isset($content->sections) && count($content->sections) > 0) ? count($content->sections) : 0 }};
    const sectionsContainer = document.getElementById('sections-container');
    const emptyState = document.getElementById('empty-state');
    
    function addSection() {
        if (emptyState) {
            emptyState.remove();
        }
        
        const sectionHtml = `
            <div class="border rounded-lg p-4 bg-gray-50 section-item" data-index="${sectionIndex}">
                <div class="flex items-start justify-between mb-2">
                    <span class="text-sm font-medium text-gray-500">Section ${sectionIndex + 1}</span>
                    <button type="button" 
                            class="remove-section-btn text-red-600 hover:text-red-900 text-sm">
                        Supprimer
                    </button>
                </div>
                
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
                    <input type="text" 
                           name="sections[${sectionIndex}][title]"
                           class="block w-full border-gray-300 rounded-md shadow-sm"
                           placeholder="Ex: Conditions Générales"
                           required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contenu</label>
                    <textarea name="sections[${sectionIndex}][content]"
                              rows="4"
                              class="block w-full border-gray-300 rounded-md shadow-sm"
                              placeholder="Contenu de la section..."
                              required></textarea>
                </div>
            </div>
        `;
        
        sectionsContainer.insertAdjacentHTML('beforeend', sectionHtml);
        sectionIndex++;
        updateSectionNumbers();
    }
    
    function removeSection(button) {
        const sectionItem = button.closest('.section-item');
        sectionItem.remove();
        updateSectionNumbers();
        
        // Show empty state if no sections remain
        if (sectionsContainer.querySelectorAll('.section-item').length === 0) {
            const emptyStateHtml = `
                <div id="empty-state" class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                    <p class="text-gray-500 mb-4">Aucune section créée.</p>
                    <button type="button" 
                            id="add-first-section-btn"
                            class="btn-logo-primary text-white font-bold py-2 px-4 rounded">
                        + Ajouter Première Section
                    </button>
                </div>
            `;
            sectionsContainer.insertAdjacentHTML('beforeend', emptyStateHtml);
            document.getElementById('add-first-section-btn').addEventListener('click', addSection);
        }
    }
    
    function updateSectionNumbers() {
        const sections = sectionsContainer.querySelectorAll('.section-item');
        sections.forEach((section, index) => {
            const numberSpan = section.querySelector('.text-gray-500');
            if (numberSpan) {
                numberSpan.textContent = `Section ${index + 1}`;
            }
        });
    }
    
    // Add section buttons
    document.getElementById('add-section-btn')?.addEventListener('click', addSection);
    document.getElementById('add-first-section-btn')?.addEventListener('click', addSection);
    
    // Remove section buttons
    sectionsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-section-btn')) {
            removeSection(e.target);
        }
    });
});
</script>
@endsection
