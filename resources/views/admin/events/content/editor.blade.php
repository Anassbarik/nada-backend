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
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="block text-sm font-medium text-gray-700">Points/Arguments</label>
                                            <button type="button" 
                                                    class="add-point-btn text-sm text-blue-600 hover:text-blue-800 font-medium"
                                                    data-section-index="{{ $index }}">
                                                + Ajouter Point
                                            </button>
                                        </div>
                                        <div class="points-container space-y-2" data-section-index="{{ $index }}">
                                            @php
                                                $points = $section['points'] ?? [];
                                                // If points is empty but content exists, parse it
                                                if (empty($points) && isset($section['content'])) {
                                                    $contentText = $section['content'] ?? '';
                                                    $lines = explode("\n", $contentText);
                                                    foreach ($lines as $line) {
                                                        $line = trim($line);
                                                        if (!empty($line)) {
                                                            // Remove dash prefixes (hyphen, en-dash, em-dash) if present
                                                            $point = preg_replace('/^[-\x{2013}\x{2014}]\s*/u', '', $line);
                                                            $point = trim($point);
                                                            if (!empty($point)) {
                                                                $points[] = $point;
                                                            }
                                                        }
                                                    }
                                                    if (empty($points) && !empty($contentText)) {
                                                        $cleanedContent = preg_replace('/^[-\x{2013}\x{2014}]\s*/u', '', trim($contentText));
                                                        if (!empty($cleanedContent)) {
                                                            $points[] = $cleanedContent;
                                                        }
                                                    }
                                                }
                                                // Clean existing points to remove any dash prefixes
                                                $points = array_map(function($point) {
                                                    return preg_replace('/^[-\x{2013}\x{2014}]\s*/u', '', trim($point));
                                                }, $points);
                                                // Ensure we have at least one point
                                                if (empty($points)) {
                                                    $points = [''];
                                                }
                                            @endphp
                                            @foreach($points as $pointIndex => $point)
                                                <div class="point-item flex items-center gap-2">
                                                    <input type="text" 
                                                           name="sections[{{ $index }}][points][{{ $pointIndex }}]"
                                                           value="{{ old("sections.{$index}.points.{$pointIndex}", $point) }}"
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
                                        @error("sections.{$index}.points") 
                                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                                        @enderror
                                        @error("sections.{$index}.points.*") 
                                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
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
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-700">Points/Arguments</label>
                        <button type="button" 
                                class="add-point-btn text-sm text-blue-600 hover:text-blue-800 font-medium"
                                data-section-index="${sectionIndex}">
                            + Ajouter Point
                        </button>
                    </div>
                    <div class="points-container space-y-2" data-section-index="${sectionIndex}">
                        <div class="point-item flex items-center gap-2">
                            <input type="text" 
                                   name="sections[${sectionIndex}][points][0]"
                                   class="flex-1 bg-white text-gray-900 border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm"
                                   placeholder="Point 1..."
                                   required>
                            <button type="button" 
                                    class="remove-point-btn text-red-600 hover:text-red-800 text-sm font-medium px-2"
                                    style="display: none;">
                                Supprimer
                            </button>
                        </div>
                    </div>
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
    
    // Add point functionality
    function addPoint(sectionIndex) {
        const pointsContainer = document.querySelector(`.points-container[data-section-index="${sectionIndex}"]`);
        if (!pointsContainer) return;
        
        const pointIndex = pointsContainer.querySelectorAll('.point-item').length;
        const pointHtml = `
            <div class="point-item flex items-center gap-2">
                <input type="text" 
                       name="sections[${sectionIndex}][points][${pointIndex}]"
                       class="flex-1 bg-white text-gray-900 border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm"
                       placeholder="Point ${pointIndex + 1}..."
                       required>
                <button type="button" 
                        class="remove-point-btn text-red-600 hover:text-red-800 text-sm font-medium px-2">
                    Supprimer
                </button>
            </div>
        `;
        
        pointsContainer.insertAdjacentHTML('beforeend', pointHtml);
        updateRemoveButtonsVisibility(sectionIndex);
    }
    
    // Remove point functionality
    function removePoint(button) {
        const pointItem = button.closest('.point-item');
        const pointsContainer = pointItem.closest('.points-container');
        const sectionIndex = pointsContainer.getAttribute('data-section-index');
        
        pointItem.remove();
        
        // Re-index remaining points
        reindexPoints(sectionIndex);
        updateRemoveButtonsVisibility(sectionIndex);
    }
    
    // Re-index points to ensure sequential array keys
    function reindexPoints(sectionIndex) {
        const pointsContainer = document.querySelector(`.points-container[data-section-index="${sectionIndex}"]`);
        if (!pointsContainer) return;
        
        const pointItems = pointsContainer.querySelectorAll('.point-item');
        pointItems.forEach((item, index) => {
            const input = item.querySelector('input[type="text"]');
            if (input) {
                input.name = `sections[${sectionIndex}][points][${index}]`;
                input.placeholder = `Point ${index + 1}...`;
            }
        });
    }
    
    // Update remove button visibility (hide if only one point remains)
    function updateRemoveButtonsVisibility(sectionIndex) {
        const pointsContainer = document.querySelector(`.points-container[data-section-index="${sectionIndex}"]`);
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
            addPoint(sectionIndex);
        }
        
        if (e.target.classList.contains('remove-point-btn')) {
            removePoint(e.target);
        }
    });
    
    // Initialize remove button visibility for existing sections
    document.querySelectorAll('.points-container').forEach(container => {
        const sectionIndex = container.getAttribute('data-section-index');
        updateRemoveButtonsVisibility(sectionIndex);
    });
});
</script>
@endsection
