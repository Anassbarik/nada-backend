@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold">{{ __('Contenu des Pages') }}: {{ $event->name }}</h1>
    </div>

    <div class="mb-4">
        <a href="{{ route('admin.events.index') }}" class="text-logo-link hover:underline inline-flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Retour aux Événements
        </a>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6">
            <h3 class="text-lg font-medium mb-6">{{ $event->name }} → Contenu des Pages</h3>
            
            @php
              $canEdit = $event->canBeEditedBy(auth()->user());
            @endphp
            
            @if(!$canEdit)
              <div class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                  <strong>View Only:</strong> This event was created by a super administrator. You can view the content but cannot modify it.
                </p>
              </div>
            @endif
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="block p-6 border-2 border-gray-300 rounded-lg {{ $canEdit ? 'hover:border-logo-link transition-colors cursor-pointer' : 'opacity-50 cursor-not-allowed' }} {{ $canEdit ? '' : 'bg-gray-50 dark:bg-gray-800' }}"
                     @if($canEdit) onclick="window.location='{{ route('admin.events.content.edit', [$event, 'conditions']) }}'" @endif>
                    <div class="text-3xl mb-2"><i data-lucide="file-text" class="w-8 h-8" style="color: #00adf1;"></i></div>
                    <h4 class="text-lg font-semibold mb-2">Conditions de Réservation</h4>
                    <p class="text-sm text-gray-500">
                        @if(isset($contents['conditions']))
                            {{ count($contents['conditions']->sections ?? []) }} section(s)
                        @else
                            Non créé
                        @endif
                    </p>
                    @if($canEdit)
                      <span class="text-logo-link text-sm mt-2 inline-block">Éditer la page →</span>
                    @else
                      <span class="text-gray-400 text-sm mt-2 inline-block" title="You cannot edit content for events created by super administrators">View Only →</span>
                    @endif
                </div>

                <div class="block p-6 border-2 border-gray-300 rounded-lg {{ $canEdit ? 'hover:border-logo-link transition-colors cursor-pointer' : 'opacity-50 cursor-not-allowed' }} {{ $canEdit ? '' : 'bg-gray-50 dark:bg-gray-800' }}"
                     @if($canEdit) onclick="window.location='{{ route('admin.events.content.edit', [$event, 'informations']) }}'" @endif>
                    <div class="text-3xl mb-2"><i data-lucide="clipboard-list" class="w-8 h-8" style="color: #00adf1;"></i></div>
                    <h4 class="text-lg font-semibold mb-2">Informations Générales</h4>
                    <p class="text-sm text-gray-500">
                        @if(isset($contents['informations']))
                            {{ count($contents['informations']->sections ?? []) }} section(s)
                        @else
                            Non créé
                        @endif
                    </p>
                    @if($canEdit)
                      <span class="text-logo-link text-sm mt-2 inline-block">Éditer la page →</span>
                    @else
                      <span class="text-gray-400 text-sm mt-2 inline-block" title="You cannot edit content for events created by super administrators">View Only →</span>
                    @endif
                </div>

                <div class="block p-6 border-2 border-gray-300 rounded-lg {{ $canEdit ? 'hover:border-logo-link transition-colors cursor-pointer' : 'opacity-50 cursor-not-allowed' }} {{ $canEdit ? '' : 'bg-gray-50 dark:bg-gray-800' }}"
                     @if($canEdit) onclick="window.location='{{ route('admin.events.content.edit', [$event, 'faq']) }}'" @endif>
                    <div class="text-3xl mb-2"><i data-lucide="help-circle" class="w-8 h-8" style="color: #00adf1;"></i></div>
                    <h4 class="text-lg font-semibold mb-2">FAQ</h4>
                    <p class="text-sm text-gray-500">
                        @if(isset($contents['faq']))
                            {{ count($contents['faq']->sections ?? []) }} section(s)
                        @else
                            Non créé
                        @endif
                    </p>
                    @if($canEdit)
                      <span class="text-logo-link text-sm mt-2 inline-block">Éditer la page →</span>
                    @else
                      <span class="text-gray-400 text-sm mt-2 inline-block" title="You cannot edit content for events created by super administrators">View Only →</span>
                    @endif
                </div>
            </div>
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>
@endsection
