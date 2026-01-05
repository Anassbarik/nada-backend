<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin - {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <style>
        :root {
            --logo-yellow: #f7cb00;
            --logo-orange: #ea5d25;
            --logo-dark-pink: #b71354;
            --logo-pink: #eb0b69;
            --logo-purple: #8c1790;
            --logo-cyan: #00adf1;
            --logo-teal: #37ac9c;
            --logo-green: #83ce2f;
        }
        .btn-logo-primary {
            background-color: #00adf1;
            color: white;
        }
        .btn-logo-primary:hover {
            background-color: #0099d8;
        }
        .text-logo-link {
            color: #00adf1;
        }
        .text-logo-link:hover {
            color: #0099d8;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        {{-- Modern Sidebar --}}
        <aside class="w-64 bg-white border-r shadow-lg flex flex-col">
            <div class="p-6 border-b bg-white">
                <div class="bg-gray-100 rounded-lg p-3 flex items-center justify-center">
                    <img src="{{ asset('assets/logo-seminaireexpo.png') }}" 
                         alt="SeminairExpo Logo" 
                         class="h-10 w-auto object-contain">
                </div>
            </div>
            
            <nav class="p-4 space-y-1">
                <a href="{{ route('dashboard') }}" 
                   class="group relative flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200
                          @if(request()->routeIs('dashboard'))
                            shadow-md font-semibold
                          @else
                            hover:bg-gray-50 text-gray-700 hover:text-gray-900
                          @endif"
                          @if(request()->routeIs('dashboard'))
                          style="background-color: rgba(0, 173, 241, 0.1); color: #00adf1; border-left: 4px solid #00adf1;"
                          @endif>
                    <i data-lucide="layout-dashboard" class="w-5 h-5 transition-colors" style="@if(request()->routeIs('dashboard')) color: #00adf1; @else color: #6b7280; @endif"></i>
                    <span>Dashboard</span>
                    @if(request()->routeIs('dashboard'))
                        <div class="ml-auto w-2 h-2 rounded-full animate-pulse" style="background-color: #00adf1;"></div>
                    @endif
                </a>
                
                <a href="{{ route('admin.events.index') }}" 
                   class="group relative flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200
                          @if(request()->routeIs('admin.events.*'))
                            shadow-md font-semibold
                          @else
                            hover:bg-gray-50 text-gray-700 hover:text-gray-900
                          @endif"
                          @if(request()->routeIs('admin.events.*'))
                          style="background-color: rgba(131, 206, 47, 0.1); color: #83ce2f; border-left: 4px solid #83ce2f;"
                          @endif>
                    <i data-lucide="calendar" class="w-5 h-5 transition-colors" style="@if(request()->routeIs('admin.events.*')) color: #83ce2f; @else color: #6b7280; @endif"></i>
                    <span>Events</span>
                    @if(request()->routeIs('admin.events.*'))
                        <div class="ml-auto w-2 h-2 rounded-full animate-pulse" style="background-color: #83ce2f;"></div>
                    @endif
                </a>
                
                <a href="{{ route('admin.bookings.index') }}" 
                   class="group relative flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200
                          @if(request()->routeIs('admin.bookings.*'))
                            shadow-md font-semibold
                          @else
                            hover:bg-gray-50 text-gray-700 hover:text-gray-900
                          @endif"
                          @if(request()->routeIs('admin.bookings.*'))
                          style="background-color: rgba(140, 23, 144, 0.1); color: #8c1790; border-left: 4px solid #8c1790;"
                          @endif>
                    <i data-lucide="file-text" class="w-5 h-5 transition-colors" style="@if(request()->routeIs('admin.bookings.*')) color: #8c1790; @else color: #6b7280; @endif"></i>
                    <span>Bookings</span>
                    @if(request()->routeIs('admin.bookings.*'))
                        <div class="ml-auto w-2 h-2 rounded-full animate-pulse" style="background-color: #8c1790;"></div>
                    @endif
                </a>
            </nav>
        </aside>
        
        {{-- Main Content Area with Header --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            {{-- Top Header --}}
            <x-admin-header />
            
            <main class="flex-1 overflow-auto">
                <div class="p-8 max-w-7xl mx-auto">
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    
    @stack('scripts')
    @livewireScripts
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
</body>
</html>

