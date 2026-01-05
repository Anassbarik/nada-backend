<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Bookings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="GET" action="{{ route('admin.bookings.index') }}" class="mb-4 flex gap-4">
                        <div>
                            <input type="text" name="search" placeholder="Search by reference, name, email, phone..." value="{{ request('search') }}" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm px-3 py-2">
                        </div>
                        <div>
                            <select name="status" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm px-3 py-2">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <select name="event_id" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm px-3 py-2">
                                <option value="">All Events</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>{{ $event->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-primary-button type="submit">Filter</x-primary-button>
                        <a href="{{ route('admin.bookings.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Clear</a>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"></th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ref</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Guest</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Event</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Hotel</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Package</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($bookings as $booking)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button onclick="toggleDetails({{ $booking->id }})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="icon-{{ $booking->id }}">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $booking->booking_reference ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            <div>{{ $booking->full_name ?? $booking->guest_name ?? 'N/A' }}</div>
                                            <div class="text-xs text-gray-500">{{ $booking->email ?? $booking->guest_email ?? '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $booking->event->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $booking->hotel->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $booking->package->nom_package ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-800' : ($booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $booking->created_at->format('Y-m-d') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <form method="POST" action="{{ route('admin.bookings.updateStatus', $booking) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status" onchange="this.form.submit()" class="text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded">
                                                    <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                                    <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                    <!-- Expanded Details Row -->
                                    <tr id="details-{{ $booking->id }}" class="hidden">
                                        <td colspan="9" class="px-6 py-4 bg-gray-50 dark:bg-gray-900">
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                                                <!-- Client Information -->
                                                <div class="space-y-2">
                                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Client Information</h4>
                                                    <div><span class="font-medium">Full Name:</span> {{ $booking->full_name ?? $booking->guest_name ?? 'N/A' }}</div>
                                                    <div><span class="font-medium">Company:</span> {{ $booking->company ?? 'N/A' }}</div>
                                                    <div><span class="font-medium">Email:</span> {{ $booking->email ?? $booking->guest_email ?? 'N/A' }}</div>
                                                    <div><span class="font-medium">Phone:</span> {{ $booking->phone ?? $booking->guest_phone ?? 'N/A' }}</div>
                                                </div>

                                                <!-- Flight Information -->
                                                <div class="space-y-2">
                                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Flight Information</h4>
                                                    <div><span class="font-medium">Flight Number:</span> {{ $booking->flight_number ?? 'N/A' }}</div>
                                                    <div><span class="font-medium">Flight Date:</span> {{ $booking->flight_date ? $booking->flight_date->format('Y-m-d') : 'N/A' }}</div>
                                                    <div><span class="font-medium">Flight Time:</span> {{ $booking->flight_time ? $booking->flight_time->format('H:i') : 'N/A' }}</div>
                                                    <div><span class="font-medium">Airport:</span> {{ $booking->airport ?? 'N/A' }}</div>
                                                </div>

                                                <!-- Booking Details -->
                                                <div class="space-y-2">
                                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Booking Details</h4>
                                                    <div><span class="font-medium">Booking Reference:</span> {{ $booking->booking_reference ?? 'N/A' }}</div>
                                                    <div><span class="font-medium">Check-in Date:</span> {{ $booking->checkin_date ? $booking->checkin_date->format('Y-m-d') : 'N/A' }}</div>
                                                    <div><span class="font-medium">Check-out Date:</span> {{ $booking->checkout_date ? $booking->checkout_date->format('Y-m-d') : 'N/A' }}</div>
                                                    <div><span class="font-medium">Guests Count:</span> {{ $booking->guests_count ?? 'N/A' }}</div>
                                                </div>

                                                <!-- Event & Hotel -->
                                                <div class="space-y-2">
                                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Event & Hotel</h4>
                                                    <div><span class="font-medium">Event:</span> {{ $booking->event->name ?? 'N/A' }}</div>
                                                    <div><span class="font-medium">Hotel:</span> {{ $booking->hotel->name ?? 'N/A' }}</div>
                                                    <div><span class="font-medium">Package:</span> {{ $booking->package->nom_package ?? 'N/A' }}</div>
                                                    <div><span class="font-medium">Package Type:</span> {{ $booking->package->type_chambre ?? 'N/A' }}</div>
                                                </div>

                                                <!-- Resident Names -->
                                                <div class="space-y-2">
                                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Resident Names</h4>
                                                    <div><span class="font-medium">Resident 1:</span> {{ $booking->resident_name_1 ?? 'N/A' }}</div>
                                                    <div><span class="font-medium">Resident 2:</span> {{ $booking->resident_name_2 ?? 'N/A' }}</div>
                                                </div>

                                                <!-- Special Instructions -->
                                                <div class="space-y-2 md:col-span-2 lg:col-span-3">
                                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Special Instructions / Requests</h4>
                                                    <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded">
                                                        {{ $booking->special_instructions ?? $booking->special_requests ?? 'None' }}
                                                    </div>
                                                </div>

                                                <!-- Legacy Fields (if different from new fields) -->
                                                @if($booking->guest_name && $booking->guest_name !== ($booking->full_name ?? ''))
                                                    <div class="space-y-2 md:col-span-2 lg:col-span-3">
                                                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Legacy Fields</h4>
                                                        <div class="grid grid-cols-3 gap-2 text-xs">
                                                            <div><span class="font-medium">Guest Name:</span> {{ $booking->guest_name }}</div>
                                                            <div><span class="font-medium">Guest Email:</span> {{ $booking->guest_email ?? 'N/A' }}</div>
                                                            <div><span class="font-medium">Guest Phone:</span> {{ $booking->guest_phone ?? 'N/A' }}</div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Timestamps -->
                                                <div class="space-y-2 md:col-span-2 lg:col-span-3">
                                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Timestamps</h4>
                                                    <div class="grid grid-cols-2 gap-2 text-xs text-gray-500">
                                                        <div><span class="font-medium">Created:</span> {{ $booking->created_at->format('Y-m-d H:i:s') }}</div>
                                                        <div><span class="font-medium">Updated:</span> {{ $booking->updated_at->format('Y-m-d H:i:s') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No bookings found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $bookings->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleDetails(bookingId) {
            const detailsRow = document.getElementById('details-' + bookingId);
            const icon = document.getElementById('icon-' + bookingId);
            
            if (detailsRow.classList.contains('hidden')) {
                detailsRow.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                detailsRow.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        }
    </script>
</x-app-layout>
