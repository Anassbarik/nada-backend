@extends('layouts.admin')

@section('content')
<div class="space-y-6">
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">Event Packages</h1>
  </div>
  
  <x-shadcn.card class="shadow-lg">
    <x-shadcn.card-content class="p-12">
      <div class="text-center">
        <div class="mb-6">
          <i data-lucide="package" class="w-24 h-24 mx-auto text-gray-400"></i>
        </div>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Coming Soon</h2>
        <p class="text-gray-600 text-lg">Event Packages functionality will be available soon.</p>
      </div>
    </x-shadcn.card-content>
  </x-shadcn.card>
</div>
@endsection





