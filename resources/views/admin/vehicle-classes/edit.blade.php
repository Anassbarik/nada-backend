@extends('layouts.admin')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Edit Vehicle Class</h1>
            <a href="{{ route('admin.vehicle-classes.index') }}" class="text-blue-600 hover:underline">Back to List</a>
        </div>

        <x-shadcn.card>
            <x-shadcn.card-content class="p-6">
                <form action="{{ route('admin.vehicle-classes.update', $vehicleClass) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" value="Class Name" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $vehicleClass->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end">
                        <x-primary-button class="btn-logo-primary">
                            Update Class
                        </x-primary-button>
                    </div>
                </form>
            </x-shadcn.card-content>
        </x-shadcn.card>
    </div>
@endsection