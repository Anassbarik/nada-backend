@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Transfers</h1>
                <p class="text-gray-500">Manage transfers for {{ $accommodation->name }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.events.index') }}"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Back to Events
                </a>
                @if($accommodation->canManageTransfersBy(auth()->user()))
                    <a href="{{ route('admin.transfers.exportAccommodation', $accommodation) }}"
                        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors shadow-sm flex items-center gap-2">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        Export Excel
                    </a>
                    <a href="{{ route('admin.vehicle-types.index') }}"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2">
                        <i data-lucide="settings" class="w-4 h-4"></i>
                        Vehicles
                    </a>
                    <a href="{{ route('admin.transfers.create', $accommodation) }}"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        New Transfer
                    </a>
                @endif
            </div>
        </div>

        <!-- Stats / Summary (Optional) -->

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3">Client</th>
                            <th class="px-6 py-3">Type</th>
                            <th class="px-6 py-3">Date/Time</th>
                            <th class="px-6 py-3">Route</th>
                            <th class="px-6 py-3">Vehicle</th>
                            <th class="px-6 py-3">Price</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($transfers as $transfer)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $transfer->client_name }}</div>
                                    <div class="text-gray-500 text-xs">{{ $transfer->client_phone }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $transfer->transfer_type_label }}
                                    </span>
                                    <div class="text-xs text-gray-500 mt-1">{{ $transfer->trip_type_label }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-gray-900">{{ $transfer->transfer_date->format('d M Y') }}</div>
                                    <div class="text-gray-500 text-xs">
                                        {{ \Carbon\Carbon::parse($transfer->pickup_time)->format('H:i') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1 text-gray-900">
                                        <span class="font-medium">From:</span> {{ Str::limit($transfer->pickup_location, 15) }}
                                    </div>
                                    <div class="flex items-center gap-1 text-gray-500 mt-0.5">
                                        <span class="font-medium">To:</span> {{ Str::limit($transfer->dropoff_location, 15) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-gray-900">{{ $transfer->vehicle_type_label }}</div>
                                    <div class="text-gray-500 text-xs">{{ $transfer->passengers }} pax</div>
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ number_format($transfer->price, 2) }} €
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusClasses = match ($transfer->status) {
                                            'confirmed' => 'bg-green-100 text-green-800',
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            'completed' => 'bg-gray-100 text-gray-800',
                                            'paid' => 'bg-blue-100 text-blue-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses }}">
                                        {{ ucfirst($transfer->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($transfer->eticket_url)
                                            <a href="{{ $transfer->eticket_url }}" target="_blank"
                                                class="p-1 text-gray-500 hover:text-blue-600 transition-colors"
                                                title="Download eTicket">
                                                <i data-lucide="file-text" class="w-4 h-4"></i>
                                            </a>
                                        @endif

                                        <a href="{{ route('admin.transfers.edit', ['accommodation' => $accommodation, 'transfer' => $transfer]) }}"
                                            class="p-1 text-gray-500 hover:text-blue-600 transition-colors" title="Edit">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </a>

                                        <form action="{{ route('admin.transfers.duplicate', [$accommodation, $transfer]) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="p-1 text-gray-500 hover:text-indigo-600 transition-colors"
                                                title="Duplicate">
                                                <i data-lucide="copy" class="w-4 h-4"></i>
                                            </button>
                                        </form>

                                        @if($accommodation->canManageTransfersBy(auth()->user()))
                                            <a href="{{ route('admin.transfers.exportSingle', [$accommodation, $transfer]) }}"
                                                class="p-1 text-gray-500 hover:text-green-600 transition-colors"
                                                title="Export to Excel">
                                                <i data-lucide="download" class="w-4 h-4"></i>
                                            </a>
                                        @endif

                                        <form
                                            action="{{ route('admin.transfers.destroy', ['accommodation' => $accommodation, 'transfer' => $transfer]) }}"
                                            method="POST" class="inline"
                                            onsubmit="return confirm('Are you sure? This will also delete the associated booking.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 text-gray-500 hover:text-red-600 transition-colors"
                                                title="Delete">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    <i data-lucide="car" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
                                    <p class="text-lg font-medium text-gray-900">No transfers found</p>
                                    <p class="mb-4">Get started by creating a new transfer for this event.</p>
                                    @if($accommodation->canManageTransfersBy(auth()->user()))
                                        <a href="{{ route('admin.transfers.create', $accommodation) }}"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                                            Create Transfer
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transfers->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $transfers->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection