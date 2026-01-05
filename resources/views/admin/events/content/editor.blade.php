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
            @livewire('event-content-editor', ['event' => $event, 'pageType' => $pageType])
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>
@endsection
