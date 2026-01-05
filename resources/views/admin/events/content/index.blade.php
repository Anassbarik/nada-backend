@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold">{{ __('pages') }}: {{ $event->name }}</h1>
    </div>

    <div class="mb-4">
        <a href="{{ route('admin.events.index') }}" class="text-logo-link hover:underline inline-flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Retour aux Événements
        </a>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6">
            <h3 class="text-lg font-medium mb-6">{{ $event->name }} → {{ __('pages') }}</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('admin.events.content.edit', [$event, 'conditions']) }}" class="block p-6 border-2 border-gray-300 rounded-lg hover:border-logo-link transition-colors">
                    <div class="text-3xl mb-2"><i data-lucide="file-text" class="w-8 h-8" style="color: #00adf1;"></i></div>
                    <h4 class="text-lg font-semibold mb-2">Conditions de Réservation</h4>
                    <p class="text-sm text-gray-500">
                        @if(isset($contents['conditions']))
                            {{ count($contents['conditions']->sections ?? []) }} section(s)
                        @else
                            Non créé
                        @endif
                    </p>
                    <span class="text-logo-link text-sm mt-2 inline-block">Éditer la page →</span>
                </a>

                <a href="{{ route('admin.events.content.edit', [$event, 'informations']) }}" class="block p-6 border-2 border-gray-300 rounded-lg hover:border-logo-link transition-colors">
                    <div class="text-3xl mb-2"><i data-lucide="clipboard-list" class="w-8 h-8" style="color: #00adf1;"></i></div>
                    <h4 class="text-lg font-semibold mb-2">Informations Générales</h4>
                    <p class="text-sm text-gray-500">
                        @if(isset($contents['informations']))
                            {{ count($contents['informations']->sections ?? []) }} section(s)
                        @else
                            Non créé
                        @endif
                    </p>
                    <span class="text-logo-link text-sm mt-2 inline-block">Éditer la page →</span>
                </a>

                <a href="{{ route('admin.events.content.edit', [$event, 'faq']) }}" class="block p-6 border-2 border-gray-300 rounded-lg hover:border-logo-link transition-colors">
                    <div class="text-3xl mb-2"><i data-lucide="help-circle" class="w-8 h-8" style="color: #00adf1;"></i></div>
                    <h4 class="text-lg font-semibold mb-2">FAQ</h4>
                    <p class="text-sm text-gray-500">
                        @if(isset($contents['faq']))
                            {{ count($contents['faq']->sections ?? []) }} section(s)
                        @else
                            Non créé
                        @endif
                    </p>
                    <span class="text-logo-link text-sm mt-2 inline-block">Éditer la page →</span>
                </a>
            </div>
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>
@endsection
