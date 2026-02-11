@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">Vehicle Types</h1>
                <p class="text-gray-600 mt-1">Manage vehicle types for transfers.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.vehicle-classes.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-logo-primary focus:ring-offset-2 transition ease-in-out duration-150">
                    <i data-lucide="settings-2" class="w-4 h-4 mr-2"></i>
                    Vehicle Classes
                </a>
                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('transfers', 'create'))
                    <a href="{{ route('admin.vehicle-types.create') }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-logo-primary focus:ring-offset-2 transition ease-in-out duration-150 btn-logo-primary">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                        Add Vehicle Type
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
                                <th class="px-6 py-4 text-sm font-semibold text-gray-700">Class</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-700 uppercase">Max Passengers</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-700 uppercase">Max Luggages</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-700 uppercase text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($vehicleTypes as $type)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $type->name }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-600">{{ $type->vehicleClass->name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-600">{{ $type->max_passengers }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-600">{{ $type->max_luggages }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.vehicle-types.edit', $type) }}"
                                                class="text-blue-600 hover:text-blue-900" title="Edit">
                                                <i data-lucide="edit-2" class="w-4 h-4"></i>
                                            </a>
                                            <form action="{{ route('admin.vehicle-types.destroy', $type) }}" method="POST"
                                                class="inline"
                                                onsubmit="return confirm('Are you sure you want to delete this vehicle type?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                        No vehicle types found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-shadcn.card-content>
        </x-shadcn.card>

        <div class="mt-4">
            {{ $vehicleTypes->links() }}
        </div>
    </div>
@endsection