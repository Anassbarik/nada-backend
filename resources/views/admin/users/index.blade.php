@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">Users Management</h1>
    </div>

    {{-- Filters --}}
    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-4">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search by name, email, phone, or company..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <select name="role" 
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Roles</option>
                        <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>Regular Users</option>
                        <option value="organizer" {{ request('role') === 'organizer' ? 'selected' : '' }}>Organizers</option>
                    </select>
                </div>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                    Filter
                </button>
                @if(request('search') || request('role'))
                <a href="{{ route('admin.users.index') }}" 
                   class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition-colors">
                    Clear
                </a>
                @endif
            </form>
        </x-shadcn.card-content>
    </x-shadcn.card>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-0">
            <x-shadcn.table responsive>
                <x-shadcn.table-header>
                    <x-shadcn.table-row>
                        <x-shadcn.table-head>Name</x-shadcn.table-head>
                        <x-shadcn.table-head>Email</x-shadcn.table-head>
                        <x-shadcn.table-head>Role</x-shadcn.table-head>
                        <x-shadcn.table-head>Phone</x-shadcn.table-head>
                        <x-shadcn.table-head>Company</x-shadcn.table-head>
                        <x-shadcn.table-head>Bookings</x-shadcn.table-head>
                        <x-shadcn.table-head>Actions</x-shadcn.table-head>
                    </x-shadcn.table-row>
                </x-shadcn.table-header>
                <x-shadcn.table-body>
                    @forelse($users as $user)
                        <x-shadcn.table-row hover>
                            <x-shadcn.table-cell class="font-medium break-words">{{ $user->name }}</x-shadcn.table-cell>
                            <x-shadcn.table-cell class="break-all">{{ $user->email }}</x-shadcn.table-cell>
                            <x-shadcn.table-cell>
                                <x-shadcn.badge variant="{{ $user->role === 'organizer' ? 'default' : 'secondary' }}">
                                    {{ ucfirst($user->role) }}
                                </x-shadcn.badge>
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell class="break-all">{{ $user->phone ?? '—' }}</x-shadcn.table-cell>
                            <x-shadcn.table-cell class="break-words">{{ $user->company ?? '—' }}</x-shadcn.table-cell>
                            <x-shadcn.table-cell>
                                <span class="text-sm text-gray-600">{{ $user->bookings_count ?? 0 }}</span>
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell>
                                <div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm">
                                    <a href="{{ route('admin.users.show', $user) }}" 
                                       class="text-blue-600 hover:text-blue-900 whitespace-nowrap">
                                        View
                                    </a>
                                    @can('impersonate', $user)
                                    <form method="POST" action="{{ route('admin.users.impersonate', $user) }}" class="inline" 
                                          target="_blank"
                                          onsubmit="return confirm('Are you sure you want to impersonate {{ $user->name }}? {{ $user->role === 'user' ? 'You will be redirected to the frontend dashboard in a new tab.' : 'You will be logged in as this user in a new tab.' }}');">
                                        @csrf
                                        <button type="submit" class="text-purple-600 hover:text-purple-900 whitespace-nowrap">
                                            Impersonate
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </x-shadcn.table-cell>
                        </x-shadcn.table-row>
                    @empty
                        <x-shadcn.table-row>
                            <x-shadcn.table-cell colspan="7" class="text-center text-muted-foreground">No users found</x-shadcn.table-cell>
                        </x-shadcn.table-row>
                    @endforelse
                </x-shadcn.table-body>
            </x-shadcn.table>
        </x-shadcn.card-content>
    </x-shadcn.card>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
@endsection

