<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-950">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'LeadBot') — Lead Generation System</title>

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#f0fdf4',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                        }
                    }
                }
            }
        }
    </script>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .sidebar-link { @apply flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-gray-400 hover:bg-gray-800 hover:text-white transition-all duration-150; }
        .sidebar-link.active { @apply bg-gray-800 text-white; }
    </style>

    @stack('head')
</head>
<body class="h-full bg-gray-950 text-gray-100" x-data="{ sidebarOpen: true }">

<div class="flex h-full">

    {{-- ─── Sidebar ──────────────────────────────────────────────────── --}}
    <aside class="w-64 bg-gray-900 border-r border-gray-800 flex flex-col flex-shrink-0">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-800">
            <div class="w-8 h-8 rounded-lg bg-green-500 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <div>
                <div class="font-bold text-white text-sm">LeadBot</div>
                <div class="text-xs text-gray-500">Lead Generation</div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1">
            <div class="text-xs font-semibold text-gray-600 uppercase tracking-wider px-4 mb-2">Menu</div>

            <a href="{{ route('dashboard') }}"
               class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            <a href="{{ route('leads.index') }}"
               class="sidebar-link {{ request()->routeIs('leads.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Leads
                <span class="ml-auto bg-green-500 text-white text-xs rounded-full px-2 py-0.5">
                    {{ \App\Models\Lead::count() }}
                </span>
            </a>

            <a href="{{ route('leads.export') }}"
               class="sidebar-link">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export CSV
            </a>
        </nav>

        {{-- Footer --}}
        <div class="px-3 py-4 border-t border-gray-800">
            <div class="flex items-center gap-3 px-4 py-2.5">
                <div class="w-7 h-7 rounded-full bg-green-500 flex items-center justify-center text-xs font-bold text-white">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="px-4 mt-1">
                @csrf
                <button type="submit"
                        class="text-xs text-gray-500 hover:text-red-400 transition-colors">
                    Sign out
                </button>
            </form>
        </div>
    </aside>

    {{-- ─── Main Content ─────────────────────────────────────────────── --}}
    <main class="flex-1 flex flex-col overflow-hidden">

        {{-- Top bar --}}
        <header class="h-16 bg-gray-900 border-b border-gray-800 flex items-center justify-between px-6 flex-shrink-0">
            <div>
                <h1 class="text-lg font-semibold text-white">@yield('page-title', 'Dashboard')</h1>
                <p class="text-xs text-gray-500">@yield('page-subtitle', now()->format('l, F j Y'))</p>
            </div>
            <div class="flex items-center gap-3">
                {{-- Today's count badge --}}
                <div class="flex items-center gap-2 bg-gray-800 rounded-lg px-3 py-1.5 text-sm">
                    <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                    <span class="text-gray-400">Today:</span>
                    <span class="text-white font-medium">
                        {{ \App\Models\Lead::whereDate('created_at', today())->count() }} leads
                    </span>
                </div>
            </div>
        </header>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="mx-6 mt-4 bg-green-900/50 border border-green-700 text-green-300 text-sm rounded-lg px-4 py-3 flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error') || $errors->any())
            <div class="mx-6 mt-4 bg-red-900/50 border border-red-700 text-red-300 text-sm rounded-lg px-4 py-3">
                @if (session('error'))
                    {{ session('error') }}
                @else
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                @endif
            </div>
        @endif

        {{-- Page content --}}
        <div class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </div>
    </main>
</div>

@stack('scripts')
</body>
</html>
