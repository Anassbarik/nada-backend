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
            <x-shadcn.table-cell class="space-x-2">
              <a href="{{ route('admin.events.edit', $event) }}" class="text-logo-link hover:underline">Edit</a>
              <a href="{{ route('admin.events.content.index', $event) }}" class="text-purple-600 hover:underline" title="{{ __('pages') }}">{{ __('pages') }}</a>
              <a href="{{ route('admin.events.hotels.index', $event) }}" class="text-indigo-600 hover:underline">{{ __('hotels') }}</a>
              <form action="{{ route('admin.events.destroy', $event) }}" method="POST" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('{{ __('Are you sure you want to delete this event?') }}')">Delete</button>
              </form>
            </x-shadcn.table-cell>
          </x-shadcn.table-row>
          @empty
          <x-shadcn.table-row>
            <x-shadcn.table-cell colspan="6" class="text-center text-muted-foreground">{{ __('no_events') }}</x-shadcn.table-cell>
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
