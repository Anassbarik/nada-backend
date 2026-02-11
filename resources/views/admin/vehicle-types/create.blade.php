@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">Create Vehicle Type</h1>
                <p class="text-gray-600 mt-1">Define a new vehicle type for transfers.</p>
            </div>
            <a href="{{ route('admin.vehicle-types.index') }}"
                class="text-logo-link hover:underline inline-flex items-center">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Back to Vehicle Types
            </a>
        </div>

        <x-shadcn.card class="shadow-lg">
            <x-shadcn.card-content class="p-6">
                <form method="POST" action="{{ route('admin.vehicle-types.store') }}">
                    @csrf

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="name" :value="__('Vehicle Type Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                    :value="old('name')" required autofocus placeholder="e.g., Luxury Sedan" />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            <div>
                                <div class="flex justify-between items-center">
                                    <x-input-label for="vehicle_class_id" :value="__('Vehicle Class')" />
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('transfers', 'create'))
                                        <a href="{{ route('admin.vehicle-classes.create') }}" class="text-xs text-logo-link hover:underline flex items-center">
                                            <i data-lucide="plus" class="w-3 h-3 mr-1"></i>
                                            Add New Class
                                        </a>
                                    @endif
                                </div>
                                <select id="vehicle_class_id" name="vehicle_class_id" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Sélectionner une classe</option>
                                    @foreach($vehicleClasses as $class)
                                        <option value="{{ $class->id }}" {{ old('vehicle_class_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('vehicle_class_id')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="max_passengers" :value="__('Max Passengers')" />
                                <x-text-input id="max_passengers" class="block mt-1 w-full" type="number" name="max_passengers"
                                    :value="old('max_passengers', 1)" required min="1" />
                                <p class="mt-1 text-xs text-gray-500">Strictly enforced during transfer creation.</p>
                                <x-input-error :messages="$errors->get('max_passengers')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="max_luggages" :value="__('Max Luggages')" />
                                <x-text-input id="max_luggages" class="block mt-1 w-full" type="number" name="max_luggages"
                                    :value="old('max_luggages', 0)" required min="0" />
                                <x-input-error :messages="$errors->get('max_luggages')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('admin.vehicle-types.index') }}"
                            class="text-gray-600 hover:text-gray-900 mr-4">
                            Cancel
                        </a>
                        <x-primary-button class="btn-logo-primary">
                            Create Vehicle Type
                        </x-primary-button>
                    </div>
                </form>
            </x-shadcn.card-content>
        </x-shadcn.card>
    </div>
@endsection
