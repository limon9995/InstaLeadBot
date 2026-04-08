<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'LeadBot') — Lead Generation System</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 500: '#22c55e', 600: '#16a34a' }
                    }
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        body { background: #030712; }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 14px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 500;
            color: #9ca3af;
            text-decoration: none;
            transition: background 0.15s, color 0.15s;
            white-space: nowrap;
        }
        .nav-link:hover { background: #1f2937; color: #fff; }
        .nav-link.active  { background: #1f2937; color: #fff; }
        .nav-link svg { flex-shrink: 0; width: 18px; height: 18px; }
    </style>

    @stack('head')
</head>
<body class="h-screen flex overflow-hidden text-gray-100">

{{-- ── Sidebar ────────────────────────────────────────────────────────── --}}
<aside style="width:240px; min-width:240px; background:#111827; border-right:1px solid #1f2937; display:flex; flex-direction:column;">

    {{-- Logo --}}
    <div style="display:flex; align-items:center; gap:12px; padding:20px 20px 16px; border-bottom:1px solid #1f2937;">
        <div style="width:34px; height:34px; border-radius:9px; background:#22c55e; display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:0 0 12px rgba(34,197,94,.3)">
            <svg style="width:18px;height:18px;color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
        </div>
        <div>
            <div style="font-weight:700; font-size:15px; color:#fff; line-height:1.2;">LeadBot</div>
            <div style="font-size:11px; color:#4b5563;">Lead Generation</div>
        </div>
    </div>

    {{-- Nav --}}
    <nav style="flex:1; padding:12px 10px; display:flex; flex-direction:column; gap:2px;">
        <div style="font-size:10px; font-weight:600; color:#4b5563; letter-spacing:.08em; text-transform:uppercase; padding:0 10px; margin-bottom:6px;">Menu</div>

        <a href="{{ route('dashboard') }}"
           class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('leads.index') }}"
           class="nav-link {{ request()->routeIs('leads.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Leads</span>
            <span style="margin-left:auto; background:#22c55e; color:#fff; font-size:10px; font-weight:700; border-radius:999px; padding:1px 7px;">
                {{ \App\Models\Lead::count() }}
            </span>
        </a>

        <a href="{{ route('leads.export') }}" class="nav-link">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span>Export CSV</span>
        </a>
    </nav>

    {{-- User footer --}}
    <div style="padding:12px 10px; border-top:1px solid #1f2937;">
        <div style="display:flex; align-items:center; gap:10px; padding:8px 10px;">
            <div style="width:30px; height:30px; border-radius:50%; background:#22c55e; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; color:#fff; flex-shrink:0;">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div style="min-width:0;">
                <div style="font-size:13px; font-weight:500; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ auth()->user()->name }}</div>
                <div style="font-size:11px; color:#4b5563; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ auth()->user()->email }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="padding:0 10px;">
            @csrf
            <button type="submit" style="font-size:12px; color:#4b5563; background:none; border:none; cursor:pointer; padding:0;" onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='#4b5563'">
                Sign out
            </button>
        </form>
    </div>
</aside>

{{-- ── Main ───────────────────────────────────────────────────────────── --}}
<main style="flex:1; display:flex; flex-direction:column; overflow:hidden; background:#030712;">

    {{-- Top bar --}}
    <header style="height:60px; background:#111827; border-bottom:1px solid #1f2937; display:flex; align-items:center; justify-content:space-between; padding:0 24px; flex-shrink:0;">
        <div>
            <div style="font-size:16px; font-weight:600; color:#fff;">@yield('page-title', 'Dashboard')</div>
            <div style="font-size:11px; color:#4b5563;">@yield('page-subtitle', now()->format('l, F j Y'))</div>
        </div>
        <div style="display:flex; align-items:center; gap:8px; background:#1f2937; border-radius:8px; padding:6px 12px; font-size:13px;">
            <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;animation:pulse 2s infinite;"></span>
            <span style="color:#9ca3af;">Today:</span>
            <span style="color:#fff;font-weight:600;">{{ \App\Models\Lead::whereDate('created_at', today())->count() }} leads</span>
        </div>
    </header>

    {{-- Flash messages --}}
    @if (session('success'))
        <div style="margin:16px 24px 0; background:rgba(20,83,45,.4); border:1px solid #166534; color:#4ade80; font-size:13px; border-radius:10px; padding:10px 16px; display:flex; align-items:center; gap:8px;">
            ✓ {{ session('success') }}
        </div>
    @endif
    @if (session('error') || $errors->any())
        <div style="margin:16px 24px 0; background:rgba(127,29,29,.4); border:1px solid #991b1b; color:#f87171; font-size:13px; border-radius:10px; padding:10px 16px;">
            @if(session('error')) {{ session('error') }} @else @foreach($errors->all() as $e) {{ $e }}<br> @endforeach @endif
        </div>
    @endif

    {{-- Content --}}
    <div style="flex:1; overflow-y:auto; padding:24px;">
        @yield('content')
    </div>
</main>

<style>
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
</style>

@stack('scripts')
</body>
</html>
