@extends('layouts.admin')

@section('content')
<div class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-4xl font-bold">Events Management</h1>
    <a href="{{ route('admin.events.create') }}" class="text-white px-8 py-3 rounded-xl font-semibold transition-all" style="background-color: #00adf1;" onmouseover="this.style.backgroundColor='#0099d8'" onmouseout="this.style.backgroundColor='#00adf1'">
      + New Event
    </a>
  </div>
  
  <x-shadcn.card class="shadow-lg">
    <x-shadcn.card-content class="p-0">
      <x-shadcn.table responsive>
        <x-shadcn.table-header>
          <x-shadcn.table-row>
            <x-shadcn.table-head>{{ __('name') }}</x-shadcn.table-head>
            <x-shadcn.table-head>{{ __('venue') }}</x-shadcn.table-head>
            <x-shadcn.table-head>{{ __('dates') }}</x-shadcn.table-head>
            <x-shadcn.table-head>{{ __('organizer') }}</x-shadcn.table-head>
            <x-shadcn.table-head>{{ __('status') }}</x-shadcn.table-head>
            <x-shadcn.table-head>{{ __('Actions') }}</x-shadcn.table-head>
          </x-shadcn.table-row>
        </x-shadcn.table-header>
        <x-shadcn.table-body>
          @forelse($events as $event)
          <x-shadcn.table-row hover>
            <x-shadcn.table-cell class="font-medium">{{ $event->name }}</x-shadcn.table-cell>
            <x-shadcn.table-cell>{{ $event->venue ?? '—' }}</x-shadcn.table-cell>
            <x-shadcn.table-cell>
              @if($event->compact_dates)
                {{ $event->compact_dates }}
              @else
                —
              @endif
            </x-shadcn.table-cell>
            <x-shadcn.table-cell>
              @if($event->organizer_logo)
                <img src="{{ $event->organizer_logo_url }}" alt="Organizer Logo" class="h-8 w-auto object-contain">
              @else
                <span class="text-gray-400">—</span>
              @endif
            </x-shadcn.table-cell>
            <x-shadcn.table-cell>
              <x-shadcn.badge variant="outline">
                {{ ucfirst($event->status) }}
              </x-shadcn.badge>
            </x-shadcn.table-cell>
            <x-shadcn.table-cell>
              @php
                $canEdit = $event->canBeEditedBy(auth()->user());
              @endphp
              <div class="flex items-center gap-2">
                @if(config('app.frontend_url'))
                  <a href="{{ config('app.frontend_url') }}/{{ $event->slug }}" 
                     target="_blank" 
                     rel="noopener noreferrer"
                     class="p-2 rounded-lg text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors group"
                     title="Visit Event Page">
                    <i data-lucide="external-link" class="w-4 h-4"></i>
                  </a>
                @endif
                @if($canEdit)
                  <a href="{{ route('admin.events.edit', $event) }}" 
                     class="p-2 rounded-lg text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors group"
                     title="Edit Event">
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                  </a>
                @else
                  <span class="p-2 rounded-lg text-gray-400 cursor-not-allowed opacity-50"
                        title="You cannot edit events created by super administrators">
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                  </span>
                @endif
                <a href="{{ route('admin.events.content.index', $event) }}" 
                   class="p-2 rounded-lg text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors group {{ !$canEdit ? 'opacity-50' : '' }}"
                   title="{{ __('content') }} {{ !$canEdit ? '(View Only)' : '' }}">
                  <i data-lucide="file-text" class="w-4 h-4"></i>
                </a>
                <a href="{{ route('admin.events.hotels.index', $event) }}" 
                   class="p-2 rounded-lg text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors group {{ !$canEdit ? 'opacity-50' : '' }}"
                   title="{{ __('hotels') }} {{ !$canEdit ? '(View Only)' : '' }}">
                  <i data-lucide="building" class="w-4 h-4"></i>
                </a>
                <a href="{{ route('admin.events.airports.index', $event) }}" 
                   class="p-2 rounded-lg text-teal-600 hover:bg-teal-50 dark:hover:bg-teal-900/20 transition-colors group {{ !$canEdit ? 'opacity-50' : '' }}"
                   title="Airports {{ !$canEdit ? '(View Only)' : '' }}">
                  <i data-lucide="plane" class="w-4 h-4"></i>
                </a>
                @if($canEdit)
                  <form action="{{ route('admin.events.duplicate', $event) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="p-2 rounded-lg text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-colors group"
                            title="Duplicate Event"
                            onclick="return confirm('{{ __('Are you sure you want to duplicate this event?') }}')">
                      <i data-lucide="copy" class="w-4 h-4"></i>
                    </button>
                  </form>
                  <form action="{{ route('admin.events.destroy', $event) }}" method="POST" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" 
                            class="p-2 rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors group"
                            title="Delete Event"
                            onclick="return confirm('{{ __('Are you sure you want to delete this event?') }}')">
                      <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                  </form>
                @else
                  <span class="p-2 rounded-lg text-gray-400 cursor-not-allowed opacity-50"
                        title="You cannot duplicate events created by super administrators">
                    <i data-lucide="copy" class="w-4 h-4"></i>
                  </span>
                  <span class="p-2 rounded-lg text-gray-400 cursor-not-allowed opacity-50"
                        title="You cannot delete events created by super administrators">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                  </span>
                @endif
              </div>
            </x-shadcn.table-cell>
          </x-shadcn.table-row>
          @empty
          <x-shadcn.table-row>
            <x-shadcn.table-cell colspan="6" class="text-center py-8">
              <p class="text-muted-foreground">{{ __('no_events') }}</p>
              <p class="text-sm text-muted-foreground mt-2">Get started by creating your first event.</p>
            </x-shadcn.table-cell>
          </x-shadcn.table-row>
          @endforelse
        </x-shadcn.table-body>
      </x-shadcn.table>
    </x-shadcn.card-content>
  </x-shadcn.card>
  
  <div class="mt-4">
    {{ $events->links() }}
  </div>
</div>
@endsection
