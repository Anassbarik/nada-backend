@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">{{ __('Logs') }}</h1>
            @if(!empty($defaultDaysApplied) && !empty($defaultDays))
                <p class="text-sm text-gray-500 mt-1">
                    {{ __('Showing last :days day(s) by default. Use date filters to view older logs.', ['days' => $defaultDays]) }}
                </p>
            @endif
        </div>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6 space-y-4">
            <form method="GET" action="{{ route('admin.logs.index') }}" class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                    <div class="lg:col-span-2">
                        <x-input-label for="q" :value="__('Search')" />
                        <x-text-input id="q" name="q" class="block mt-1 w-full" type="text" :value="request('q')" :placeholder="__('Search by admin, item name, booking reference...')" />
                        <p class="mt-1 text-xs text-gray-500">{{ __('Tip: enter 3+ characters to search.') }}</p>
                    </div>

                    <div>
                        <x-input-label for="user_id" :value="__('Admin')" />
                        <select id="user_id" name="user_id" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('All') }}</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}" {{ (string) request('user_id') === (string) $admin->id ? 'selected' : '' }}>
                                    {{ $admin->name }} ({{ $admin->role }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="action" :value="__('Action')" />
                        <select id="action" name="action" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('All') }}</option>
                            @foreach($actionOptions as $a)
                                <option value="{{ $a }}" {{ (string) request('action') === (string) $a ? 'selected' : '' }}>{{ __('log.action.' . $a) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="entity" :value="__('Type')" />
                        <select id="entity" name="entity" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('All') }}</option>
                            @foreach($entityOptions as $e)
                                <option value="{{ $e }}" {{ (string) request('entity') === (string) $e ? 'selected' : '' }}>{{ __('log.entity.' . $e) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="from" :value="__('From')" />
                        <x-text-input id="from" name="from" class="block mt-1 w-full" type="date" :value="request('from')" />
                    </div>

                    <div>
                        <x-input-label for="to" :value="__('To')" />
                        <x-text-input id="to" name="to" class="block mt-1 w-full" type="date" :value="request('to')" />
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-primary-button class="btn-logo-primary">{{ __('Filter') }}</x-primary-button>
                    <a href="{{ route('admin.logs.index') }}" class="text-gray-600 hover:text-gray-900">{{ __('Clear') }}</a>
                </div>
            </form>
        </x-shadcn.card-content>
    </x-shadcn.card>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-0">
            <x-shadcn.table responsive>
                <x-shadcn.table-header>
                    <x-shadcn.table-row>
                        <x-shadcn.table-head>{{ __('Date') }}</x-shadcn.table-head>
                        <x-shadcn.table-head>{{ __('Admin') }}</x-shadcn.table-head>
                        <x-shadcn.table-head>{{ __('Action') }}</x-shadcn.table-head>
                        <x-shadcn.table-head>{{ __('Item') }}</x-shadcn.table-head>
                        <x-shadcn.table-head class="hidden md:table-cell">{{ __('Result') }}</x-shadcn.table-head>
                        <x-shadcn.table-head>{{ __('Actions') }}</x-shadcn.table-head>
                    </x-shadcn.table-row>
                </x-shadcn.table-header>
                <x-shadcn.table-body>
                    @forelse($logs as $log)
                        <x-shadcn.table-row hover>
                            <x-shadcn.table-cell class="whitespace-nowrap">
                                <span class="sm:hidden">{{ $log->created_at->format('Y-m-d') }}</span>
                                <span class="hidden sm:inline">{{ $log->created_at->format('Y-m-d H:i:s') }}</span>
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell class="break-words">
                                <div class="font-medium">{{ $log->user?->name ?? '—' }}</div>
                                <div class="text-xs text-gray-500">{{ $log->user?->email }}</div>
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell class="whitespace-nowrap">
                                <div class="font-medium">{{ __('log.action.' . ($log->action_key ?? 'updated')) }}</div>
                                <div class="text-xs text-gray-500">{{ __('log.entity.' . ($log->entity_key ?? 'system')) }}</div>
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell class="break-words">
                                <div class="font-medium break-words">{{ $log->target_label ?? '—' }}</div>
                                @if($log->details)
                                    <div class="text-xs text-gray-500 break-words">{{ $log->details }}</div>
                                @endif
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell class="whitespace-nowrap hidden md:table-cell">
                                <x-shadcn.badge variant="{{ ($log->outcome ?? 'success') === 'failed' ? 'destructive' : 'default' }}">
                                    {{ ($log->outcome ?? 'success') === 'failed' ? __('Failed') : __('Succeeded') }}
                                </x-shadcn.badge>
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell class="whitespace-nowrap">
                                <a href="{{ route('admin.logs.show', $log) }}" class="text-logo-link hover:underline">{{ __('View') }}</a>
                            </x-shadcn.table-cell>
                        </x-shadcn.table-row>
                    @empty
                        <x-shadcn.table-row>
                            <x-shadcn.table-cell colspan="6" class="text-center text-muted-foreground">{{ __('No logs yet.') }}</x-shadcn.table-cell>
                        </x-shadcn.table-row>
                    @endforelse
                </x-shadcn.table-body>
            </x-shadcn.table>
        </x-shadcn.card-content>
    </x-shadcn.card>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
@endsection


