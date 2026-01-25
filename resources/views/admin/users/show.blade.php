@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">{{ $user->name }}</h1>
            <p class="text-gray-600 mt-1">{{ $user->email }}</p>
        </div>
        <div class="flex gap-2">
            @can('impersonate', $user)
            <form method="POST" action="{{ route('admin.users.impersonate', $user) }}" class="inline" 
                  target="_blank"
                  onsubmit="return confirm('Are you sure you want to impersonate {{ $user->name }}? {{ $user->role === 'user' ? 'You will be redirected to the frontend dashboard in a new tab.' : 'You will be logged in as this user in a new tab.' }}');">
                @csrf
                <button type="submit" class="px-4 sm:px-6 py-2 sm:py-3 rounded-xl font-semibold transition-all text-sm sm:text-base whitespace-nowrap text-white" style="background-color: #8c1790;" onmouseover="this.style.backgroundColor='#6b115f'" onmouseout="this.style.backgroundColor='#8c1790'">
                    Impersonate User
                </button>
            </form>
            @endcan
            <a href="{{ route('admin.users.index') }}" 
               class="px-4 sm:px-6 py-2 sm:py-3 rounded-xl font-semibold transition-all text-sm sm:text-base whitespace-nowrap bg-gray-200 hover:bg-gray-300 text-gray-700">
                Back to Users
            </a>
        </div>
    </div>

    {{-- User Info --}}
    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-header>
            <x-shadcn.card-title>User Information</x-shadcn.card-title>
        </x-shadcn.card-header>
        <x-shadcn.card-content>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-sm font-medium text-gray-700">Name</label>
                    <p class="mt-1 text-gray-900">{{ $user->name }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Email</label>
                    <p class="mt-1 text-gray-900">{{ $user->email }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Role</label>
                    <p class="mt-1">
                        <x-shadcn.badge variant="{{ $user->role === 'organizer' ? 'default' : 'secondary' }}">
                            {{ ucfirst($user->role) }}
                        </x-shadcn.badge>
                    </p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Phone</label>
                    <p class="mt-1 text-gray-900">{{ $user->phone ?? '—' }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Company</label>
                    <p class="mt-1 text-gray-900">{{ $user->company ?? '—' }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Registered</label>
                    <p class="mt-1 text-gray-900">{{ $user->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        </x-shadcn.card-content>
    </x-shadcn.card>

    {{-- Bookings --}}
    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-header>
            <x-shadcn.card-title>Bookings ({{ $user->bookings->count() }})</x-shadcn.card-title>
        </x-shadcn.card-header>
        <x-shadcn.card-content class="p-0">
            @if($user->bookings->count() > 0)
                <x-shadcn.table responsive>
                    <x-shadcn.table-header>
                        <x-shadcn.table-row>
                            <x-shadcn.table-head>Reference</x-shadcn.table-head>
                            <x-shadcn.table-head>Event</x-shadcn.table-head>
                            <x-shadcn.table-head>Hotel</x-shadcn.table-head>
                            <x-shadcn.table-head>Status</x-shadcn.table-head>
                            <x-shadcn.table-head>Date</x-shadcn.table-head>
                        </x-shadcn.table-row>
                    </x-shadcn.table-header>
                    <x-shadcn.table-body>
                        @foreach($user->bookings as $booking)
                            <x-shadcn.table-row hover>
                                <x-shadcn.table-cell class="font-medium">{{ $booking->booking_reference }}</x-shadcn.table-cell>
                                <x-shadcn.table-cell>{{ $booking->accommodation->name ?? '—' }}</x-shadcn.table-cell>
                                <x-shadcn.table-cell>{{ $booking->hotel->name ?? '—' }}</x-shadcn.table-cell>
                                <x-shadcn.table-cell>
                                    <x-shadcn.badge variant="{{ $booking->status === 'confirmed' ? 'default' : 'secondary' }}">
                                        {{ ucfirst($booking->status) }}
                                    </x-shadcn.badge>
                                </x-shadcn.table-cell>
                                <x-shadcn.table-cell>{{ $booking->created_at->format('M d, Y') }}</x-shadcn.table-cell>
                            </x-shadcn.table-row>
                        @endforeach
                    </x-shadcn.table-body>
                </x-shadcn.table>
            @else
                <div class="p-6 text-center text-gray-500">No bookings found</div>
            @endif
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>
@endsection

