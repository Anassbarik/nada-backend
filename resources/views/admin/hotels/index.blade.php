@extends('layouts.admin')

@section('content')
<div class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-4xl font-bold">{{ __('Hotels for') }}: {{ $event->name }}</h1>
    <a href="{{ route('admin.events.hotels.create', $event) }}" class="text-white px-8 py-3 rounded-xl font-semibold transition-all" style="background-color: #00adf1;" onmouseover="this.style.backgroundColor='#0099d8'" onmouseout="this.style.backgroundColor='#00adf1'">
      {{ __('add_hotel') }}
    </a>
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
              <x-shadcn.table-cell class="font-medium">
                {{ $hotel->name }}
                @if($hotel->rating)
                  @php $stars = $hotel->rating_stars @endphp
                  <div class="mt-1 flex items-center text-xs">
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
                @if($hotel->location_url)
                  <a href="{{ $hotel->location_url }}" target="_blank" rel="noopener noreferrer" class="text-logo-link hover:underline inline-flex items-center" onclick="event.stopPropagation();">
                    {{ $hotel->location }}
                    <i data-lucide="map-pin" class="w-4 h-4 ml-1"></i>
                  </a>
                @else
                  {{ $hotel->location }}
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
                <div class="flex items-center gap-2">
                  <a href="{{ route('admin.hotels.packages.index', $hotel) }}" 
                     class="text-logo-link hover:underline" 
                     title="{{ __('packages') }}">
                    <i data-lucide="package" class="w-4 h-4"></i>
                  </a>
                  <a href="{{ route('admin.hotels.edit', $hotel) }}" 
                     class="text-indigo-600 hover:text-indigo-900">
                    {{ __('edit') }}
                  </a>
                  <form method="POST" action="{{ route('admin.hotels.destroy', $hotel) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this hotel?') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-900">{{ __('delete') }}</button>
                  </form>
                </div>
              </x-shadcn.table-cell>
            </x-shadcn.table-row>
          @empty
            <x-shadcn.table-row>
              <x-shadcn.table-cell colspan="6" class="text-center text-muted-foreground">{{ __('no_hotels') }}</x-shadcn.table-cell>
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
