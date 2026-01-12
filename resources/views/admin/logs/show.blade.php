@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">{{ __('Log Details') }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $log->created_at->format('Y-m-d H:i:s') }}</p>
        </div>
        <a href="{{ route('admin.logs.index') }}" class="text-logo-link hover:underline inline-flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            {{ __('Back') }}
        </a>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-gray-500">{{ __('Admin') }}</div>
                    <div class="font-medium">{{ $log->user?->name ?? '—' }}</div>
                    <div class="text-sm text-gray-500">{{ $log->user?->email }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">{{ __('Action') }}</div>
                    <div class="font-medium">{{ __('log.action.' . ($log->action_key ?? 'updated')) }}</div>
                    <div class="text-sm text-gray-500">{{ __('log.entity.' . ($log->entity_key ?? 'system')) }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">{{ __('Item') }}</div>
                    <div class="text-sm font-medium break-words">{{ $log->target_label ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">{{ __('Result') }}</div>
                    <div class="text-sm">
                        <x-shadcn.badge variant="{{ ($log->outcome ?? 'success') === 'failed' ? 'destructive' : 'default' }}">
                            {{ ($log->outcome ?? 'success') === 'failed' ? __('Failed') : __('Succeeded') }}
                        </x-shadcn.badge>
                    </div>
                </div>
            </div>

            @if($log->details)
                <div class="pt-2 border-t">
                    <div class="text-sm text-gray-500 mb-1">{{ __('Details') }}</div>
                    <div class="text-sm break-words">{{ $log->details }}</div>
                </div>
            @endif
        </x-shadcn.card-content>
    </x-shadcn.card>

</div>
@endsection


