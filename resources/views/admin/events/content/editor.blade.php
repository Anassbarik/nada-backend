@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">{{ __('Éditer') }}: {{ $pageName }} - {{ $event->name }}</h1>
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
                    <p class="text-sm text-gray-600 mb-4">Veuillez remplir le contenu en anglais et en français. Les admins peuvent uniquement modifier le contenu, pas le créer de zéro.</p>
                </div>

                <!-- English Sections -->
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-md font-medium text-gray-900">Sections (English)</h4>
                        <button type="button" 
                                id="add-section-en-btn"
                                class="btn-logo-primary text-white font-bold py-2 px-4 rounded text-sm">
                            + Add Section
                        </button>
                    </div>

                    <div id="sections-en-container" class="space-y-4">
                        @php
                            $sectionsEn = [];
                            if ($content) {
                                $sectionsEn = $content->sections_en ?? [];
                                if (empty($sectionsEn) && isset($content->sections)) {
                                    $sectionsEn = $content->sections;
                                }
                            }
                        @endphp
                        @if(!empty($sectionsEn))
                            @foreach($sectionsEn as $index => $section)
                                <div class="border rounded-lg p-4 bg-blue-50 section-item-en" data-index="{{ $index }}">
                                    <div class="flex items-start justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-500">Section {{ $index + 1 }} (EN)</span>
                                        <button type="button" 
                                                class="remove-section-btn text-red-600 hover:text-red-900 text-sm">
                                            Remove
                                        </button>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Title (English)</label>
                                        <input type="text" 
                                               name="sections_en[{{ $index }}][title]"
                                               value="{{ old("sections_en.{$index}.title", $section['title'] ?? '') }}"
                                               class="block w-full border-gray-300 rounded-md shadow-sm"
                                               placeholder="e.g., Terms and Conditions"
                                               required>
                                        @error("sections_en.{$index}.title") 
                                            <span class="text-red-500 text-xs">{{ $message }}</span> 
                                        @enderror
                                    </div>
                                    
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="block text-sm font-medium text-gray-700">Points</label>
                                            <button type="button" 
                                                    class="add-point-btn text-sm text-blue-600 hover:text-blue-800 font-medium"
                                                    data-section-index="{{ $index }}"
                                                    data-lang="en">
                                                + Add Point
                                            </button>
                                        </div>
                                        <div class="points-container space-y-2" data-section-index="{{ $index }}" data-lang="en">
                                            @php
                                                $points = $section['points'] ?? [];
                                                if (empty($points)) {
                                                    $points = [''];
                                                }
                                            @endphp
                                            @foreach($points as $pointIndex => $point)
                                                <div class="point-item flex items-center gap-2">
                                                    <input type="text" 
                                                           name="sections_en[{{ $index }}][points][{{ $pointIndex }}]"
                                                           value="{{ old("sections_en.{$index}.points.{$pointIndex}", $point) }}"
                                                           class="flex-1 bg-white text-gray-900 border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm"
                                                           placeholder="Point {{ $pointIndex + 1 }}..."
                                                           required>
                                                    <button type="button" 
                                                            class="remove-point-btn text-red-600 hover:text-red-800 text-sm font-medium px-2"
                                                            @if(count($points) <= 1) style="display: none;" @endif>
                                                        Remove
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div id="empty-state-en" class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                                <p class="text-gray-500 mb-4">No sections created.</p>
                                <button type="button" 
                                        id="add-first-section-en-btn"
                                        class="btn-logo-primary text-white font-bold py-2 px-4 rounded">
                                    + Add First Section
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- French Sections -->
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-md font-medium text-gray-900">Sections (Français)</h4>
                        <button type="button" 
                                id="add-section-fr-btn"
                                class="btn-logo-primary text-white font-bold py-2 px-4 rounded text-sm">
                            + Ajouter Section
                        </button>
                    </div>

                    <div id="sections-fr-container" class="space-y-4">
                        @php
                            $sectionsFr = [];
                            if ($content) {
                                $sectionsFr = $content->sections_fr ?? [];
                                if (empty($sectionsFr) && isset($content->sections)) {
                                    $sectionsFr = $content->sections;
                                }
                            }
                        @endphp
                        @if(!empty($sectionsFr))
                            @foreach($sectionsFr as $index => $section)
                                <div class="border rounded-lg p-4 bg-green-50 section-item-fr" data-index="{{ $index }}">
                                    <div class="flex items-start justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-500">Section {{ $index + 1 }} (FR)</span>
                                        <button type="button" 
                                                class="remove-section-btn text-red-600 hover:text-red-900 text-sm">
                                            Supprimer
                                        </button>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Titre (Français)</label>
                                        <input type="text" 
                                               name="sections_fr[{{ $index }}][title]"
                                               value="{{ old("sections_fr.{$index}.title", $section['title'] ?? '') }}"
                                               class="block w-full border-gray-300 rounded-md shadow-sm"
                                               placeholder="Ex: Conditions Générales"
                                               required>
                                        @error("sections_fr.{$index}.title") 
                                            <span class="text-red-500 text-xs">{{ $message }}</span> 
                                        @enderror
                                    </div>
                                    
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="block text-sm font-medium text-gray-700">Points</label>
                                            <button type="button" 
                                                    class="add-point-btn text-sm text-blue-600 hover:text-blue-800 font-medium"
                                                    data-section-index="{{ $index }}"
                                                    data-lang="fr">
                                                + Ajouter Point
                                            </button>
                                        </div>
                                        <div class="points-container space-y-2" data-section-index="{{ $index }}" data-lang="fr">
                                            @php
                                                $points = $section['points'] ?? [];
                                                if (empty($points)) {
                                                    $points = [''];
                                                }
                                            @endphp
                                            @foreach($points as $pointIndex => $point)
                                                <div class="point-item flex items-center gap-2">
                                                    <input type="text" 
                                                           name="sections_fr[{{ $index }}][points][{{ $pointIndex }}]"
                                                           value="{{ old("sections_fr.{$index}.points.{$pointIndex}", $point) }}"
                                                           class="flex-1 bg-white text-gray-900 border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm"
                                                           placeholder="Point {{ $pointIndex + 1 }}..."
                                                           required>
                                                    <button type="button" 
                                                            class="remove-point-btn text-red-600 hover:text-red-800 text-sm font-medium px-2"
                                                            @if(count($points) <= 1) style="display: none;" @endif>
                                                        Supprimer
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div id="empty-state-fr" class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                                <p class="text-gray-500 mb-4">Aucune section créée.</p>
                                <button type="button" 
                                        id="add-first-section-fr-btn"
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
    @php
        $sectionsEnCount = 0;
        $sectionsFrCount = 0;
        if ($content) {
            $sectionsEn = $content->sections_en ?? [];
            if (empty($sectionsEn) && isset($content->sections)) {
                $sectionsEn = $content->sections;
            }
            $sectionsEnCount = count($sectionsEn);
            
            $sectionsFr = $content->sections_fr ?? [];
            if (empty($sectionsFr) && isset($content->sections)) {
                $sectionsFr = $content->sections;
            }
            $sectionsFrCount = count($sectionsFr);
        }
    @endphp
    let sectionIndexEn = {{ $sectionsEnCount }};
    let sectionIndexFr = {{ $sectionsFrCount }};
    const sectionsEnContainer = document.getElementById('sections-en-container');
    const sectionsFrContainer = document.getElementById('sections-fr-container');
    
    // Helper function to add section
    function addSection(lang) {
        const container = lang === 'en' ? sectionsEnContainer : sectionsFrContainer;
        const emptyState = container.querySelector(`#empty-state-${lang}`);
        const sectionIndex = lang === 'en' ? sectionIndexEn : sectionIndexFr;
        
        if (emptyState) {
            emptyState.remove();
        }
        
        const bgColor = lang === 'en' ? 'bg-blue-50' : 'bg-green-50';
        const sectionLabel = lang === 'en' ? 'Section' : 'Section';
        const titleLabel = lang === 'en' ? 'Title (English)' : 'Titre (Français)';
        const titlePlaceholder = lang === 'en' ? 'e.g., Terms and Conditions' : 'Ex: Conditions Générales';
        const pointsLabel = lang === 'en' ? 'Points' : 'Points';
        const addPointText = lang === 'en' ? '+ Add Point' : '+ Ajouter Point';
        const removeText = lang === 'en' ? 'Remove' : 'Supprimer';
        const pointPlaceholder = lang === 'en' ? 'Point' : 'Point';
        const namePrefix = lang === 'en' ? 'sections_en' : 'sections_fr';
        
        const sectionHtml = `
            <div class="border rounded-lg p-4 ${bgColor} section-item-${lang}" data-index="${sectionIndex}">
                <div class="flex items-start justify-between mb-2">
                    <span class="text-sm font-medium text-gray-500">${sectionLabel} ${sectionIndex + 1} (${lang.toUpperCase()})</span>
                    <button type="button" 
                            class="remove-section-btn text-red-600 hover:text-red-900 text-sm">
                        ${removeText}
                    </button>
                </div>
                
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">${titleLabel}</label>
                    <input type="text" 
                           name="${namePrefix}[${sectionIndex}][title]"
                           class="block w-full border-gray-300 rounded-md shadow-sm"
                           placeholder="${titlePlaceholder}"
                           required>
                </div>
                
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-700">${pointsLabel}</label>
                        <button type="button" 
                                class="add-point-btn text-sm text-blue-600 hover:text-blue-800 font-medium"
                                data-section-index="${sectionIndex}"
                                data-lang="${lang}">
                            ${addPointText}
                        </button>
                    </div>
                    <div class="points-container space-y-2" data-section-index="${sectionIndex}" data-lang="${lang}">
                        <div class="point-item flex items-center gap-2">
                            <input type="text" 
                                   name="${namePrefix}[${sectionIndex}][points][0]"
                                   class="flex-1 bg-white text-gray-900 border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm"
                                   placeholder="${pointPlaceholder} 1..."
                                   required>
                            <button type="button" 
                                    class="remove-point-btn text-red-600 hover:text-red-800 text-sm font-medium px-2"
                                    style="display: none;">
                                ${removeText}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', sectionHtml);
        if (lang === 'en') {
            sectionIndexEn++;
        } else {
            sectionIndexFr++;
        }
        updateSectionNumbers(lang);
    }
    
    function removeSection(button, lang) {
        const sectionItem = button.closest(`.section-item-${lang}`);
        const container = lang === 'en' ? sectionsEnContainer : sectionsFrContainer;
        sectionItem.remove();
        updateSectionNumbers(lang);
        
        if (container.querySelectorAll(`.section-item-${lang}`).length === 0) {
            const emptyStateHtml = `
                <div id="empty-state-${lang}" class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                    <p class="text-gray-500 mb-4">${lang === 'en' ? 'No sections created.' : 'Aucune section créée.'}</p>
                    <button type="button" 
                            id="add-first-section-${lang}-btn"
                            class="btn-logo-primary text-white font-bold py-2 px-4 rounded">
                        ${lang === 'en' ? '+ Add First Section' : '+ Ajouter Première Section'}
                    </button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', emptyStateHtml);
            document.getElementById(`add-first-section-${lang}-btn`).addEventListener('click', () => addSection(lang));
        }
    }
    
    function updateSectionNumbers(lang) {
        const container = lang === 'en' ? sectionsEnContainer : sectionsFrContainer;
        const sections = container.querySelectorAll(`.section-item-${lang}`);
        sections.forEach((section, index) => {
            const numberSpan = section.querySelector('.text-gray-500');
            if (numberSpan) {
                numberSpan.textContent = `Section ${index + 1} (${lang.toUpperCase()})`;
            }
        });
    }
    
    // Add section buttons
    document.getElementById('add-section-en-btn')?.addEventListener('click', () => addSection('en'));
    document.getElementById('add-section-fr-btn')?.addEventListener('click', () => addSection('fr'));
    document.getElementById('add-first-section-en-btn')?.addEventListener('click', () => addSection('en'));
    document.getElementById('add-first-section-fr-btn')?.addEventListener('click', () => addSection('fr'));
    
    // Remove section buttons
    sectionsEnContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-section-btn')) {
            removeSection(e.target, 'en');
        }
    });
    sectionsFrContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-section-btn')) {
            removeSection(e.target, 'fr');
        }
    });
    
    // Add point functionality
    function addPoint(sectionIndex, lang) {
        const namePrefix = lang === 'en' ? 'sections_en' : 'sections_fr';
        const pointsContainer = document.querySelector(`.points-container[data-section-index="${sectionIndex}"][data-lang="${lang}"]`);
        if (!pointsContainer) return;
        
        const pointIndex = pointsContainer.querySelectorAll('.point-item').length;
        const removeText = lang === 'en' ? 'Remove' : 'Supprimer';
        const pointPlaceholder = lang === 'en' ? 'Point' : 'Point';
        
        const pointHtml = `
            <div class="point-item flex items-center gap-2">
                <input type="text" 
                       name="${namePrefix}[${sectionIndex}][points][${pointIndex}]"
                       class="flex-1 bg-white text-gray-900 border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm"
                       placeholder="${pointPlaceholder} ${pointIndex + 1}..."
                       required>
                <button type="button" 
                        class="remove-point-btn text-red-600 hover:text-red-800 text-sm font-medium px-2">
                    ${removeText}
                </button>
            </div>
        `;
        
        pointsContainer.insertAdjacentHTML('beforeend', pointHtml);
        updateRemoveButtonsVisibility(sectionIndex, lang);
    }
    
    // Remove point functionality
    function removePoint(button, lang) {
        const pointItem = button.closest('.point-item');
        const pointsContainer = pointItem.closest('.points-container');
        const sectionIndex = pointsContainer.getAttribute('data-section-index');
        
        pointItem.remove();
        reindexPoints(sectionIndex, lang);
        updateRemoveButtonsVisibility(sectionIndex, lang);
    }
    
    // Re-index points
    function reindexPoints(sectionIndex, lang) {
        const namePrefix = lang === 'en' ? 'sections_en' : 'sections_fr';
        const pointsContainer = document.querySelector(`.points-container[data-section-index="${sectionIndex}"][data-lang="${lang}"]`);
        if (!pointsContainer) return;
        
        const pointItems = pointsContainer.querySelectorAll('.point-item');
        const pointPlaceholder = lang === 'en' ? 'Point' : 'Point';
        pointItems.forEach((item, index) => {
            const input = item.querySelector('input[type="text"]');
            if (input) {
                input.name = `${namePrefix}[${sectionIndex}][points][${index}]`;
                input.placeholder = `${pointPlaceholder} ${index + 1}...`;
            }
        });
    }
    
    // Update remove button visibility
    function updateRemoveButtonsVisibility(sectionIndex, lang) {
        const pointsContainer = document.querySelector(`.points-container[data-section-index="${sectionIndex}"][data-lang="${lang}"]`);
        if (!pointsContainer) return;
        
        const pointItems = pointsContainer.querySelectorAll('.point-item');
        const removeButtons = pointsContainer.querySelectorAll('.remove-point-btn');
        
        removeButtons.forEach(btn => {
            btn.style.display = pointItems.length > 1 ? '' : 'none';
        });
    }
    
    // Handle add point button clicks
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-point-btn')) {
            const sectionIndex = e.target.getAttribute('data-section-index');
            const lang = e.target.getAttribute('data-lang');
            addPoint(sectionIndex, lang);
        }
        
        if (e.target.classList.contains('remove-point-btn')) {
            const pointsContainer = e.target.closest('.points-container');
            const lang = pointsContainer.getAttribute('data-lang');
            removePoint(e.target, lang);
        }
    });
    
    // Initialize remove button visibility
    document.querySelectorAll('.points-container').forEach(container => {
        const sectionIndex = container.getAttribute('data-section-index');
        const lang = container.getAttribute('data-lang');
        updateRemoveButtonsVisibility(sectionIndex, lang);
    });
});
</script>
@endsection
