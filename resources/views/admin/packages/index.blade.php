@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold">{{ __('packages_for') }}: {{ $hotel->name }}</h1>
    </div>

    <div class="mb-4">
        <a href="{{ route('admin.events.hotels.index', $hotel->event) }}" 
           class="text-logo-link hover:underline inline-flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            {{ __('back_to_hotels') }}
        </a>
    </div>

    {{-- Success/Error Messages --}}
    @if (session('success'))
        <x-alert type="success" class="mb-6">
            {{ session('success') }}
        </x-alert>
    @endif

    @if ($errors->any())
        <x-alert type="error" class="mb-6">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    {{-- New Package Button --}}
    <div class="mb-8">
        <a href="{{ route('admin.hotels.packages.create', $hotel) }}" 
           class="btn-logo-primary px-6 py-2 text-white rounded-lg transition-colors inline-block">
            {{ __('new_package') }}
        </a>
    </div>

    {{-- Packages Table --}}
    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-header>
            <x-shadcn.card-title>{{ $hotel->name }} ({{ $packages->total() }} {{ __('packages') }})</x-shadcn.card-title>
        </x-shadcn.card-header>
        <x-shadcn.card-content class="p-0">
            <x-shadcn.table responsive>
                <x-shadcn.table-header>
                    <x-shadcn.table-row>
                        <x-shadcn.table-head>{{ __('nom_package') }}</x-shadcn.table-head>
                        <x-shadcn.table-head>{{ __('type_chambre') }}</x-shadcn.table-head>
                        <x-shadcn.table-head>{{ __('occupants') }}</x-shadcn.table-head>
                        <x-shadcn.table-head>{{ __('prix_ttc') }}</x-shadcn.table-head>
                        <x-shadcn.table-head>{{ __('chambres_restantes') }}</x-shadcn.table-head>
                        <x-shadcn.table-head>{{ __('disponibilite') }}</x-shadcn.table-head>
                        <x-shadcn.table-head>{{ __('Actions') }}</x-shadcn.table-head>
                    </x-shadcn.table-row>
                </x-shadcn.table-header>
                <x-shadcn.table-body>
                    @forelse($packages as $package)
                        <x-shadcn.table-row hover>
                            <x-shadcn.table-cell class="font-medium">{{ $package->nom_package }}</x-shadcn.table-cell>
                            <x-shadcn.table-cell>{{ $package->type_chambre }}</x-shadcn.table-cell>
                            <x-shadcn.table-cell>{{ $package->occupants }}</x-shadcn.table-cell>
                            <x-shadcn.table-cell class="font-semibold text-green-600">{{ number_format($package->prix_ttc, 2) }} MAD</x-shadcn.table-cell>
                            <x-shadcn.table-cell>
                                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-sm">
                                    {{ $package->chambres_restantes }} / {{ $package->quantite_chambres }}
                                </span>
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell>
                                <x-shadcn.badge variant="{{ $package->disponibilite ? 'default' : 'destructive' }}">
                                    {{ $package->disponibilite ? __('yes') : __('no') }}
                                </x-shadcn.badge>
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell class="space-x-2">
                                <a href="{{ route('admin.hotels.packages.edit', [$hotel, $package]) }}" 
                                   class="text-logo-link hover:underline">{{ __('edit') }}</a>
                                <form method="POST" action="{{ route('admin.hotels.packages.destroy', [$hotel, $package]) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:underline"
                                            onclick="return confirm('{{ __('Are you sure you want to delete this package?') }}')">
                                        {{ __('delete') }}
                                    </button>
                                </form>
                            </x-shadcn.table-cell>
                        </x-shadcn.table-row>
                    @empty
                        <x-shadcn.table-row>
                            <x-shadcn.table-cell colspan="7" class="text-center text-muted-foreground">
                                {{ __('no_packages') }}
                            </x-shadcn.table-cell>
                        </x-shadcn.table-row>
                    @endforelse
                </x-shadcn.table-body>
            </x-shadcn.table>
        </x-shadcn.card-content>
        @if($packages->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $packages->links() }}
            </div>
        @endif
    </x-shadcn.card>
</div>
@endsection
