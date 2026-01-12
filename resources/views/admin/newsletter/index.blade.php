@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">{{ __('Newsletter Subscribers') }}</h1>
        <a href="{{ route('admin.newsletter.create') }}" class="btn-logo-primary text-white px-4 sm:px-6 lg:px-8 py-2 sm:py-3 rounded-xl font-semibold transition-all text-sm sm:text-base whitespace-nowrap">
            {{ __('Send Newsletter') }}
        </a>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6 space-y-4">
            <form method="GET" action="{{ route('admin.newsletter.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <x-input-label for="q" :value="__('Search')" />
                    <x-text-input id="q" name="q" class="block mt-1 w-full" type="text" :value="request('q')" placeholder="email, name..." />
                </div>

                <div>
                    <x-input-label for="status" :value="__('Status')" />
                    <select id="status" name="status" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">{{ __('All') }}</option>
                        <option value="subscribed" {{ request('status') === 'subscribed' ? 'selected' : '' }}>{{ __('Subscribed') }}</option>
                        <option value="unsubscribed" {{ request('status') === 'unsubscribed' ? 'selected' : '' }}>{{ __('Unsubscribed') }}</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <x-primary-button class="btn-logo-primary">{{ __('Filter') }}</x-primary-button>
                    <a href="{{ route('admin.newsletter.index') }}" class="text-gray-600 hover:text-gray-900">{{ __('Clear') }}</a>
                </div>
            </form>
        </x-shadcn.card-content>
    </x-shadcn.card>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-0">
            <x-shadcn.table responsive>
                <x-shadcn.table-header>
                    <x-shadcn.table-row>
                        <x-shadcn.table-head>{{ __('Email') }}</x-shadcn.table-head>
                        <x-shadcn.table-head class="hidden sm:table-cell">{{ __('Name') }}</x-shadcn.table-head>
                        <x-shadcn.table-head class="hidden md:table-cell">{{ __('Subscribed') }}</x-shadcn.table-head>
                        <x-shadcn.table-head>{{ __('Status') }}</x-shadcn.table-head>
                        <x-shadcn.table-head>{{ __('Actions') }}</x-shadcn.table-head>
                    </x-shadcn.table-row>
                </x-shadcn.table-header>
                <x-shadcn.table-body>
                    @forelse($subscribers as $subscriber)
                        <x-shadcn.table-row hover>
                            <x-shadcn.table-cell class="font-medium break-words">
                                {{ $subscriber->email }}
                                @if($subscriber->name)
                                    <div class="text-xs text-gray-500 sm:hidden mt-1">{{ $subscriber->name }}</div>
                                @endif
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell class="hidden sm:table-cell break-words">
                                {{ $subscriber->name ?? '—' }}
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell class="hidden md:table-cell whitespace-nowrap">
                                {{ $subscriber->subscribed_at->format('Y-m-d H:i') }}
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell>
                                <x-shadcn.badge variant="{{ $subscriber->status === 'subscribed' ? 'default' : 'secondary' }}">
                                    {{ $subscriber->status === 'subscribed' ? __('Subscribed') : __('Unsubscribed') }}
                                </x-shadcn.badge>
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell>
                                <div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm">
                                    @if($subscriber->status === 'subscribed')
                                        <form method="POST" action="{{ route('admin.newsletter.unsubscribe', $subscriber) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to unsubscribe this subscriber?') }}');">
                                            @csrf
                                            <button type="submit" class="text-orange-600 hover:underline whitespace-nowrap">{{ __('Unsubscribe') }}</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.newsletter.resubscribe', $subscriber) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:underline whitespace-nowrap">{{ __('Re-subscribe') }}</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.newsletter.destroy', $subscriber) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this subscriber?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline whitespace-nowrap">{{ __('delete') }}</button>
                                    </form>
                                </div>
                            </x-shadcn.table-cell>
                        </x-shadcn.table-row>
                    @empty
                        <x-shadcn.table-row>
                            <x-shadcn.table-cell colspan="5" class="text-center text-muted-foreground">{{ __('No subscribers found.') }}</x-shadcn.table-cell>
                        </x-shadcn.table-row>
                    @endforelse
                </x-shadcn.table-body>
            </x-shadcn.table>
        </x-shadcn.card-content>
    </x-shadcn.card>

    <div class="mt-4">
        {{ $subscribers->links() }}
    </div>
</div>
@endsection

