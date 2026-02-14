@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">All Transfers</h1>
                <p class="text-gray-500">Global view of all transfers across accommodations</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.vehicle-types.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-logo-primary focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    <i data-lucide="settings" class="w-4 h-4 mr-2"></i>
                    Vehicle Types
                </a>
                <a href="{{ route('admin.vehicle-classes.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-logo-primary focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    <i data-lucide="settings-2" class="w-4 h-4 mr-2"></i>
                    Vehicle Classes
                </a>
                <a href="{{ route('admin.transfers.exportAll') }}"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-logo-primary focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                    Export All
                </a>
                <a href="{{ route('admin.transfers.create-standalone') }}"
                    class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-white shadow-sm"
                    style="background-color: #00adf1;" onmouseover="this.style.backgroundColor='#0099d8'"
                    onmouseout="this.style.backgroundColor='#00adf1'">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                    Create Transfer
                </a>
                {{-- Export All to Excel button could go here --}}
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <form action="{{ route('admin.transfers.global-index') }}" method="GET"
                class="flex flex-col md:flex-row items-end gap-4">
                <div class="flex-1 w-full">
                    <label for="accommodation_id" class="block text-sm font-medium text-gray-700 mb-1">Filter by
                        Event</label>
                    <select name="accommodation_id" id="accommodation_id"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-logo-primary focus:ring-logo-primary sm:text-sm">
                        <option value="">All Events</option>
                        @foreach($accommodations as $acc)
                            <option value="{{ $acc->id }}" {{ request('accommodation_id') == $acc->id ? 'selected' : '' }}>
                                {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2 w-full md:w-auto">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-logo-primary hover:bg-logo-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-logo-primary w-full md:w-auto justify-center"
                        style="background-color: #00adf1;">
                        <i data-lucide="filter" class="w-4 h-4 mr-2"></i>
                        Filter
                    </button>
                    @if(request('accommodation_id'))
                        <a href="{{ route('admin.transfers.global-index') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-logo-primary w-full md:w-auto justify-center">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3">Accommodation</th>
                            <th class="px-6 py-3">Client</th>
                            <th class="px-6 py-3">Type</th>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Vehicle</th>
                            <th class="px-6 py-3">Driver</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($transfers as $transfer)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ $transfer->accommodation->name }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $transfer->client_name }}</div>
                                    <div class="text-gray-500 text-xs">{{ $transfer->client_phone }}</div>
                                    @if($transfer->additional_passengers && count(array_filter($transfer->additional_passengers)) > 0)
                                        <div class="text-xs text-blue-600 mt-1 cursor-help"
                                            title="Additional Passengers: {{ implode(', ', array_filter($transfer->additional_passengers)) }}">
                                            + {{ count(array_filter($transfer->additional_passengers)) }} more passengers
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $transfer->transfer_type_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-gray-900">{{ $transfer->transfer_date->format('d M Y') }}</div>
                                    <div class="text-gray-500 text-xs">
                                        {{ \Carbon\Carbon::parse($transfer->pickup_time)->format('H:i') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    {{ $transfer->vehicle_type_label }}
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    @if($transfer->driver_name)
                                        <div class="font-medium text-gray-900">{{ $transfer->driver_name }}</div>
                                        <div class="text-gray-500">{{ $transfer->driver_phone }}</div>
                                    @else
                                        <span class="text-gray-400 italic">Not Assigned</span>
                                    @endif
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
                                        <a href="{{ route('admin.transfers.edit', ['accommodation' => $transfer->accommodation, 'transfer' => $transfer]) }}"
                                            class="p-1 text-gray-500 hover:text-blue-600 transition-colors" title="Manage">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </a>

                                        <form action="{{ route('admin.transfers.duplicate-standalone', $transfer) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="p-1 text-gray-500 hover:text-indigo-600 transition-colors"
                                                title="Duplicate">
                                                <i data-lucide="copy" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    <p>No transfers found.</p>
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