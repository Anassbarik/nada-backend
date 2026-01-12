@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">Admins Management</h1>
        @can('create', App\Models\User::class)
        <a href="{{ route('admin.admins.create') }}" class="text-white px-4 sm:px-6 lg:px-8 py-2 sm:py-3 rounded-xl font-semibold transition-all text-sm sm:text-base whitespace-nowrap" style="background-color: #00adf1;" onmouseover="this.style.backgroundColor='#0099d8'" onmouseout="this.style.backgroundColor='#00adf1'">
            + New Admin
        </a>
        @endcan
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-0">
            <x-shadcn.table responsive>
                <x-shadcn.table-header>
                    <x-shadcn.table-row>
                        <x-shadcn.table-head>Name</x-shadcn.table-head>
                        <x-shadcn.table-head>Email</x-shadcn.table-head>
                        <x-shadcn.table-head>Role</x-shadcn.table-head>
                        <x-shadcn.table-head>Permissions</x-shadcn.table-head>
                        <x-shadcn.table-head>Actions</x-shadcn.table-head>
                    </x-shadcn.table-row>
                </x-shadcn.table-header>
                <x-shadcn.table-body>
                    @forelse($admins as $admin)
                        <x-shadcn.table-row hover>
                            <x-shadcn.table-cell class="font-medium break-words">{{ $admin->name }}</x-shadcn.table-cell>
                            <x-shadcn.table-cell class="break-all">{{ $admin->email }}</x-shadcn.table-cell>
                            <x-shadcn.table-cell>
                                <x-shadcn.badge variant="{{ $admin->isSuperAdmin() ? 'default' : 'secondary' }}">
                                    {{ ucfirst(str_replace('-', ' ', $admin->role)) }}
                                </x-shadcn.badge>
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell>
                                @if($admin->isSuperAdmin())
                                    <span class="text-sm text-gray-600">All Permissions</span>
                                @else
                                    <span class="text-sm text-gray-600">{{ $admin->permissions->count() }} permission(s)</span>
                                @endif
                            </x-shadcn.table-cell>
                            <x-shadcn.table-cell>
                                <div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm">
                                    @can('update', $admin)
                                    <a href="{{ route('admin.admins.edit', $admin) }}" 
                                       class="text-blue-600 hover:text-blue-900 whitespace-nowrap">
                                        Edit
                                    </a>
                                    @endcan
                                    @can('delete', $admin)
                                    <form method="POST" action="{{ route('admin.admins.destroy', $admin) }}" class="inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this admin?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 whitespace-nowrap">Delete</button>
                                    </form>
                                    @endcan
                                </div>
                            </x-shadcn.table-cell>
                        </x-shadcn.table-row>
                    @empty
                        <x-shadcn.table-row>
                            <x-shadcn.table-cell colspan="5" class="text-center text-muted-foreground">No admins found</x-shadcn.table-cell>
                        </x-shadcn.table-row>
                    @endforelse
                </x-shadcn.table-body>
            </x-shadcn.table>
        </x-shadcn.card-content>
    </x-shadcn.card>

    <div class="mt-4">
        {{ $admins->links() }}
    </div>
</div>
@endsection

