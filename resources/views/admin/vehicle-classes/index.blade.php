@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">Vehicle Classes</h1>
                <p class="text-gray-600 mt-1">Manage categories for vehicle types (e.g., Standard, Business).</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.vehicle-types.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-logo-primary focus:ring-offset-2 transition ease-in-out duration-150">
                    <i data-lucide="settings" class="w-4 h-4 mr-2"></i>
                    Vehicle Types
                </a>
                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('transfers', 'create'))
                    <a href="{{ route('admin.vehicle-classes.create') }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-logo-primary focus:ring-offset-2 transition ease-in-out duration-150 btn-logo-primary">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                        Add Vehicle Class
                    </a>
                @endif
            </div>
        </div>

        <x-shadcn.card class="shadow-lg">
            <x-shadcn.card-content class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-6 py-4 text-sm font-semibold text-gray-700">Name</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-700 uppercase">Created At</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-700 uppercase text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($vehicleClasses as $class)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $class->name }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-600">{{ $class->created_at->format('Y-m-d H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            @if(auth()->user()->hasPermission('transfers', 'edit'))
                                                <a href="{{ route('admin.vehicle-classes.edit', $class) }}"
                                                    class="text-blue-600 hover:text-blue-900" title="Edit">
                                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                                </a>
                                            @endif
                                            @if(auth()->user()->hasPermission('transfers', 'delete'))
                                                <form action="{{ route('admin.vehicle-classes.destroy', $class) }}" method="POST"
                                                    class="inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this vehicle class? This might affect vehicle types.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-10 text-center text-gray-500">
                                        No vehicle classes found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-shadcn.card-content>
        </x-shadcn.card>

        <div class="mt-4">
            {{ $vehicleClasses->links() }}
        </div>
    </div>
@endsection