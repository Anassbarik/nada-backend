@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">{{ __('packages_for') }}: {{ $hotel->name }}</h1>
    </div>

    <div class="mb-4">
        <a href="{{ route('admin.events.hotels.index', $hotel->event) }}" 
           class="text-logo-link hover:underline inline-flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            {{ __('back_to_hotels') }}
        </a>
    </div>

    {{-- New Package Button --}}
    <div class="mb-8">
        @php
          $canEdit = $hotel->event->canBeEditedBy(auth()->user());
        @endphp
        @if($canEdit)
          <a href="{{ route('admin.hotels.packages.create', $hotel) }}" 
             class="btn-logo-primary px-6 py-2 text-white rounded-lg transition-colors inline-block">
              {{ __('new_package') }}
          </a>
        @else
          <span class="text-gray-400 px-6 py-2 rounded-lg opacity-50 cursor-not-allowed inline-block" title="You cannot modify packages for events created by super administrators">
              {{ __('new_package') }} (View Only)
          </span>
        @endif
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
                                @php
                                  $canEdit = $hotel->event->canBeEditedBy(auth()->user());
                                @endphp
                                @if($canEdit)
                                  <a href="{{ route('admin.hotels.packages.edit', [$hotel, $package]) }}" 
                                     class="text-logo-link hover:underline">{{ __('edit') }}</a>
                                  <form method="POST" action="{{ route('admin.hotels.packages.duplicate', [$hotel, $package]) }}" class="inline">
                                      @csrf
                                      <button type="submit" 
                                              class="text-orange-600 hover:underline"
                                              onclick="return confirm('{{ __('Are you sure you want to duplicate this package?') }}')"
                                              title="{{ __('duplicate') }}">
                                          <i data-lucide="copy" class="w-4 h-4 inline"></i>
                                      </button>
                                  </form>
                                  <form method="POST" action="{{ route('admin.hotels.packages.destroy', [$hotel, $package]) }}" class="inline">
                                      @csrf
                                      @method('DELETE')
                                      <button type="submit" 
                                              class="text-red-600 hover:underline"
                                              onclick="return confirm('{{ __('Are you sure you want to delete this package?') }}')">
                                          {{ __('delete') }}
                                      </button>
                                  </form>
                                @else
                                  <span class="text-gray-400 opacity-50 cursor-not-allowed" title="You cannot modify packages for events created by super administrators">{{ __('edit') }}</span>
                                  <span class="text-gray-400 opacity-50 cursor-not-allowed" title="You cannot duplicate packages for events created by super administrators">
                                      <i data-lucide="copy" class="w-4 h-4 inline"></i>
                                  </span>
                                  <span class="text-gray-400 opacity-50 cursor-not-allowed" title="You cannot delete packages for events created by super administrators">{{ __('delete') }}</span>
                                @endif
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
