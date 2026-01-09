@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold">{{ __('partners') }}</h1>
        <a href="{{ route('admin.partners.create') }}" class="btn-logo-primary text-white px-8 py-3 rounded-xl font-semibold transition-all">
            {{ __('create_partner') }}
        </a>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-0">
            <x-shadcn.table responsive>
                <x-shadcn.table-header>
                    <x-shadcn.table-row>
                        <x-shadcn.table-head>{{ __('logo') }}</x-shadcn.table-head>
                        <x-shadcn.table-head>{{ __('name') }}</x-shadcn.table-head>
                        <x-shadcn.table-head>{{ __('url') }}</x-shadcn.table-head>
                        <x-shadcn.table-head>{{ __('sort_order') }}</x-shadcn.table-head>
                        <x-shadcn.table-head>{{ __('status') }}</x-shadcn.table-head>
                        <x-shadcn.table-head>{{ __('Actions') }}</x-shadcn.table-head>
                    </x-shadcn.table-row>
                </x-shadcn.table-header>
                <x-shadcn.table-body>
                    @forelse($partners as $partner)
                        <x-shadcn.table-row hover>
                            <x-shadcn.table-cell>
                                @if($partner->logo_path)
                                    <img src="{{ $partner->logo_url }}" alt="{{ $partner->name }}" class="h-12 w-auto object-contain">
                                @else
                                    <span class="text-gray-400">{{ __('no_logo') }}</span>
                                @endif
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell class="font-medium">{{ $partner->name }}</x-shadcn.table-cell>
                            <x-shadcn.table-cell>
                                @if($partner->url)
                                    <a href="{{ $partner->url }}" target="_blank" rel="noopener noreferrer" class="text-logo-link hover:underline">
                                        {{ Str::limit($partner->url, 30) }}
                                    </a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell>{{ $partner->sort_order }}</x-shadcn.table-cell>
                            <x-shadcn.table-cell>
                                <form method="POST" action="{{ route('admin.partners.toggle-active', $partner) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-2 py-1 text-xs font-semibold rounded-full {{ $partner->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $partner->active ? __('active') : __('inactive') }}
                                    </button>
                                </form>
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell class="space-x-2">
                                <a href="{{ route('admin.partners.edit', $partner) }}" class="text-logo-link hover:underline">{{ __('edit') }}</a>
                                <form method="POST" action="{{ route('admin.partners.duplicate', $partner) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to duplicate this partner?') }}');">
                                    @csrf
                                    <button type="submit" class="text-orange-600 hover:underline" title="{{ __('duplicate') }}">
                                        <i data-lucide="copy" class="w-4 h-4 inline"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.partners.destroy', $partner) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this partner?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">{{ __('delete') }}</button>
                                </form>
                            </x-shadcn.table-cell>
                        </x-shadcn.table-row>
                    @empty
                        <x-shadcn.table-row>
                            <x-shadcn.table-cell colspan="6" class="text-center text-muted-foreground">{{ __('no_partners') }}</x-shadcn.table-cell>
                        </x-shadcn.table-row>
                    @endforelse
                </x-shadcn.table-body>
            </x-shadcn.table>
        </x-shadcn.card-content>
    </x-shadcn.card>

    <div class="mt-4">
        {{ $partners->links() }}
    </div>
</div>
@endsection
