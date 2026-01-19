@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6 text-center">
            <h1 class="text-2xl font-bold mb-4">No Event Assigned</h1>
            <p class="text-gray-600">You don't have an event assigned to your organizer account. Please contact the administrator.</p>
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>
@endsection

