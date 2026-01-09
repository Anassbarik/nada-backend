@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold">Airports - {{ $event->name }}</h1>
        @php
          $canEdit = $event->canBeEditedBy(auth()->user());
        @endphp
        @if($canEdit)
          <a href="{{ route('admin.events.airports.create', $event) }}" class="btn-logo-primary text-white px-8 py-3 rounded-xl font-semibold transition-all">
              + New Airport
          </a>
        @else
          <span class="text-gray-400 px-8 py-3 rounded-xl font-semibold opacity-50 cursor-not-allowed" title="You cannot modify events created by super administrators">
              + New Airport (View Only)
          </span>
        @endif
    </div>

    <div class="mb-4">
        <a href="{{ route('admin.events.index') }}" class="text-logo-link hover:underline inline-flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Back to Events
        </a>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-0">
            <x-shadcn.table responsive>
                <x-shadcn.table-header>
                    <x-shadcn.table-row>
                        <x-shadcn.table-head>Name</x-shadcn.table-head>
                        <x-shadcn.table-head>Code</x-shadcn.table-head>
                        <x-shadcn.table-head>City / Country</x-shadcn.table-head>
                        <x-shadcn.table-head>Distance</x-shadcn.table-head>
                        <x-shadcn.table-head>Sort Order</x-shadcn.table-head>
                        <x-shadcn.table-head>Status</x-shadcn.table-head>
                        <x-shadcn.table-head>Actions</x-shadcn.table-head>
                    </x-shadcn.table-row>
                </x-shadcn.table-header>
                <x-shadcn.table-body>
                    @forelse($airports as $airport)
                        <x-shadcn.table-row hover>
                            <x-shadcn.table-cell class="font-medium">{{ $airport->name }}</x-shadcn.table-cell>
                            <x-shadcn.table-cell>
                                @if($airport->code)
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm font-mono">{{ $airport->code }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell>
                                @if($airport->city || $airport->country)
                                    {{ $airport->city }}{{ $airport->city && $airport->country ? ', ' : '' }}{{ $airport->country }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell>
                                @if($airport->distance_from_venue)
                                    {{ number_format($airport->distance_from_venue, 2) }} {{ $airport->distance_unit }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell>{{ $airport->sort_order }}</x-shadcn.table-cell>
                            <x-shadcn.table-cell>
                                <x-shadcn.badge variant="{{ $airport->active ? 'default' : 'secondary' }}">
                                    {{ $airport->active ? 'Active' : 'Inactive' }}
                                </x-shadcn.badge>
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell class="space-x-2">
                                @php
                                  $canEdit = $event->canBeEditedBy(auth()->user());
                                @endphp
                                @if($canEdit)
                                  <a href="{{ route('admin.events.airports.edit', [$event, $airport]) }}" class="text-logo-link hover:underline">Edit</a>
                                  <form method="POST" action="{{ route('admin.events.airports.duplicate', [$event, $airport]) }}" class="inline" onsubmit="return confirm('Are you sure you want to duplicate this airport?');">
                                      @csrf
                                      <button type="submit" class="text-orange-600 hover:underline" title="Duplicate">Duplicate</button>
                                  </form>
                                  <form method="POST" action="{{ route('admin.events.airports.destroy', [$event, $airport]) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this airport?');">
                                      @csrf
                                      @method('DELETE')
                                      <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                  </form>
                                @else
                                  <span class="text-gray-400 opacity-50 cursor-not-allowed" title="You cannot modify airports for events created by super administrators">Edit</span>
                                  <span class="text-gray-400 opacity-50 cursor-not-allowed" title="You cannot duplicate airports for events created by super administrators">Duplicate</span>
                                  <span class="text-gray-400 opacity-50 cursor-not-allowed" title="You cannot delete airports for events created by super administrators">Delete</span>
                                @endif
                            </x-shadcn.table-cell>
                        </x-shadcn.table-row>
                    @empty
                        <x-shadcn.table-row>
                            <x-shadcn.table-cell colspan="7" class="text-center text-muted-foreground">No airports found</x-shadcn.table-cell>
                        </x-shadcn.table-row>
                    @endforelse
                </x-shadcn.table-body>
            </x-shadcn.table>
        </x-shadcn.card-content>
    </x-shadcn.card>

    <div class="mt-4">
        {{ $airports->links() }}
    </div>
</div>
@endsection

