@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold">{{ __('Profile') }}</h1>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </x-shadcn.card-content>
    </x-shadcn.card>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </x-shadcn.card-content>
    </x-shadcn.card>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>
@endsection
