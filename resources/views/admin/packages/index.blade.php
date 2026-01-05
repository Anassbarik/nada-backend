@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold">{{ __('packages_for') }}: {{ $hotel->name }}</h1>
    </div>

    <div class="mb-4">
        <a href="{{ route('admin.events.hotels.index', $hotel->event) }}" 
           class="text-logo-link hover:underline inline-flex items-center"
           data-livewire-ignore="true">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            {{ __('back_to_hotels') }}
        </a>
    </div>

    {{-- Single Livewire Component handles both list and create --}}
    @livewire('hotel-packages-manager', ['hotel' => $hotel], key('packages-manager-' . $hotel->id))
</div>
@endsection
