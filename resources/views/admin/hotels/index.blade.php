@extends('layouts.admin')

@section('content')
<div class="space-y-6">
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">{{ __('Hotels for') }}: {{ $event->name }}</h1>
    @php
      $canEdit = $event->canBeEditedBy(auth()->user());
    @endphp
    @if($canEdit)
      <a href="{{ route('admin.events.hotels.create', $event) }}" class="text-white px-4 sm:px-6 lg:px-8 py-2 sm:py-3 rounded-xl font-semibold transition-all text-sm sm:text-base whitespace-nowrap" style="background-color: #00adf1;" onmouseover="this.style.backgroundColor='#0099d8'" onmouseout="this.style.backgroundColor='#00adf1'">
        {{ __('add_hotel') }}
      </a>
    @else
      <span class="text-gray-400 px-4 sm:px-6 lg:px-8 py-2 sm:py-3 rounded-xl font-semibold opacity-50 cursor-not-allowed text-sm sm:text-base whitespace-nowrap" title="You cannot modify events created by super administrators">
        {{ __('add_hotel') }} (View Only)
      </span>
    @endif
  </div>

  <div class="mb-4">
    <a href="{{ route('admin.events.index') }}" 
       class="text-logo-link hover:underline inline-flex items-center">
      <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
      {{ __('back_to_events') }}
    </a>
  </div>

  <x-shadcn.card class="shadow-lg">
    <x-shadcn.card-content class="p-0">
      <x-shadcn.table responsive>
        <x-shadcn.table-header>
          <x-shadcn.table-row>
            <x-shadcn.table-head>{{ __('name') }}</x-shadcn.table-head>
            <x-shadcn.table-head>{{ __('stars') }}</x-shadcn.table-head>
            <x-shadcn.table-head>{{ __('location') }}</x-shadcn.table-head>
            <x-shadcn.table-head>{{ __('Duration') }}</x-shadcn.table-head>
            <x-shadcn.table-head>{{ __('pricing') }}</x-shadcn.table-head>
            <x-shadcn.table-head>{{ __('status') }}</x-shadcn.table-head>
            <x-shadcn.table-head>{{ __('Actions') }}</x-shadcn.table-head>
          </x-shadcn.table-row>
        </x-shadcn.table-header>
        <x-shadcn.table-body>
          @forelse($hotels as $hotel)
            <x-shadcn.table-row hover onclick="window.location='{{ route('admin.hotels.images.index', $hotel) }}'" class="cursor-pointer">
              <x-shadcn.table-cell class="font-medium break-words">
                <div class="break-words">{{ $hotel->name }}</div>
                @if($hotel->rating)
                  @php $stars = $hotel->rating_stars @endphp
                  <div class="mt-1 flex items-center text-xs flex-wrap">
                    <span class="text-yellow-400">
                      {{ str_repeat('★', $stars['full']) }}
                      @if($stars['half'])★@endif
                      {{ str_repeat('☆', $stars['empty']) }}
                    </span>
                    <span class="ml-1 text-gray-600">{{ $stars['text'] }}</span>
                    @if($hotel->review_count)
                      <span class="ml-1 text-gray-500">({{ $hotel->review_count }})</span>
                    @endif
                  </div>
                @endif
              </x-shadcn.table-cell>
              <x-shadcn.table-cell>
                @if($hotel->stars)
                  <span class="text-yellow-500 font-medium">{{ str_repeat('★', (int) $hotel->stars) }}</span>
                  <span class="ml-1 text-xs text-gray-600">({{ $hotel->stars }})</span>
                @else
                  <span class="text-gray-400">—</span>
                @endif
              </x-shadcn.table-cell>
              <x-shadcn.table-cell class="break-words">
                @if($hotel->location_url)
                  <a href="{{ $hotel->location_url }}" target="_blank" rel="noopener noreferrer" class="text-logo-link hover:underline inline-flex items-center break-words" onclick="event.stopPropagation();">
                    {{ $hotel->location }}
                    <i data-lucide="map-pin" class="w-4 h-4 ml-1 flex-shrink-0"></i>
                  </a>
                @else
                  <span class="break-words">{{ $hotel->location }}</span>
                @endif
              </x-shadcn.table-cell>
              <x-shadcn.table-cell>
                @if($hotel->duration)
                  <span class="text-gray-700">{{ $hotel->duration }}</span>
                @else
                  <span class="text-gray-400">—</span>
                @endif
              </x-shadcn.table-cell>
              <x-shadcn.table-cell>
                @if($hotel->packages->where('disponibilite', true)->count() > 0)
                  <span class="text-logo-link">{{ $hotel->packages->where('disponibilite', true)->count() }} package(s) disponible(s)</span>
                @else
                  <span class="text-gray-400">Aucun package</span>
                @endif
              </x-shadcn.table-cell>
              <x-shadcn.table-cell>
                <x-shadcn.badge variant="{{ $hotel->status === 'active' ? 'default' : 'secondary' }}">
                  {{ ucfirst($hotel->status) }}
                </x-shadcn.badge>
              </x-shadcn.table-cell>
              <x-shadcn.table-cell onclick="event.stopPropagation();">
                @php
                  $canEdit = $event->canBeEditedBy(auth()->user());
                @endphp
                <div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm">
                  <a href="{{ route('admin.hotels.packages.index', $hotel) }}" 
                     class="text-logo-link hover:underline {{ !$canEdit ? 'opacity-50' : '' }} whitespace-nowrap" 
                     title="{{ __('packages') }} {{ !$canEdit ? '(View Only)' : '' }}">
                    <i data-lucide="package" class="w-4 h-4"></i>
                  </a>
                  @if($canEdit)
                    <a href="{{ route('admin.hotels.edit', $hotel) }}" 
                       class="text-indigo-600 hover:text-indigo-900 whitespace-nowrap">
                      {{ __('edit') }}
                    </a>
                    <form method="POST" action="{{ route('admin.hotels.duplicate', $hotel) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to duplicate this hotel?') }}');">
                      @csrf
                      <button type="submit" class="text-orange-600 hover:text-orange-900 whitespace-nowrap" title="{{ __('duplicate') }}">
                        <i data-lucide="copy" class="w-4 h-4"></i>
                      </button>
                    </form>
                    <form method="POST" action="{{ route('admin.hotels.destroy', $hotel) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this hotel?') }}');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="text-red-600 hover:text-red-900 whitespace-nowrap">{{ __('delete') }}</button>
                    </form>
                  @else
                    <span class="text-gray-400 opacity-50 cursor-not-allowed whitespace-nowrap" title="You cannot modify hotels for events created by super administrators">
                      {{ __('edit') }}
                    </span>
                    <span class="text-gray-400 opacity-50 cursor-not-allowed whitespace-nowrap" title="You cannot duplicate hotels for events created by super administrators">
                      <i data-lucide="copy" class="w-4 h-4"></i>
                    </span>
                    <span class="text-gray-400 opacity-50 cursor-not-allowed whitespace-nowrap" title="You cannot delete hotels for events created by super administrators">
                      {{ __('delete') }}
                    </span>
                  @endif
                </div>
              </x-shadcn.table-cell>
            </x-shadcn.table-row>
          @empty
            <x-shadcn.table-row>
              <x-shadcn.table-cell colspan="7" class="text-center text-muted-foreground">{{ __('no_hotels') }}</x-shadcn.table-cell>
            </x-shadcn.table-row>
          @endforelse
        </x-shadcn.table-body>
      </x-shadcn.table>
    </x-shadcn.card-content>
  </x-shadcn.card>
  
  <div class="mt-4">
    {{ $hotels->links() }}
  </div>
</div>
@endsection
