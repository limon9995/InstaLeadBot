@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Overview of your lead generation system')

@section('content')

{{-- ─── Scraper Control Panel ────────────────────────────────────────────── --}}
<div id="scrape-panel" style="background:linear-gradient(135deg,#0f2027,#111827); border:1px solid #1f2937; border-radius:14px; padding:20px 24px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px;">
    <div>
        <div style="font-size:15px; font-weight:700; color:#fff; margin-bottom:4px;">
            🤖 Instagram Scraper
        </div>
        <div style="font-size:12px; color:#4b5563; line-height:1.5;">
            Auto-runs daily at <strong style="color:#22c55e;">8:00 PM Bangladesh time</strong> &nbsp;·&nbsp;
            Each run fetches <strong style="color:#fff;">10 new leads</strong> from Brazil &nbsp;·&nbsp;
            Manual runs are <strong style="color:#fff;">unlimited</strong>
        </div>
        <div id="scrape-status" style="font-size:12px; color:#4b5563; margin-top:6px;">
            Next auto-run: <span style="color:#d1d5db;">Tonight 8:00 PM</span>
        </div>
    </div>
    <div style="display:flex; gap:10px; flex-shrink:0;">
        {{-- Dry Run --}}
        <button onclick="runScraper('dry')" id="btn-dry"
                style="padding:10px 18px; background:#1f2937; border:1px solid #374151; border-radius:10px; color:#9ca3af; font-size:13px; font-weight:600; cursor:pointer; transition:all .15s;"
                onmouseover="this.style.background='#374151';this.style.color='#fff'" onmouseout="this.style.background='#1f2937';this.style.color='#9ca3af'">
            🧪 Test (Dry Run)
        </button>
        {{-- Real Run --}}
        <button onclick="runScraper('real')" id="btn-run"
                style="padding:10px 22px; background:#16a34a; border:none; border-radius:10px; color:#fff; font-size:13px; font-weight:700; cursor:pointer; box-shadow:0 0 16px rgba(22,163,74,.35); transition:all .15s; display:flex; align-items:center; gap:8px;"
                onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Start Scraping
        </button>
    </div>
</div>

{{-- ─── Result Toast ─────────────────────────────────────────────────────── --}}
<div id="scrape-result" style="display:none; background:#052e16; border:1px solid #166534; border-radius:10px; padding:14px 18px; margin-bottom:16px;">
    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px;">
        <div>
            <div id="result-msg" style="font-size:13px; font-weight:600; color:#4ade80;"></div>
            <div id="result-details" style="font-size:12px; color:#6b7280; margin-top:4px;"></div>
        </div>
        <button onclick="document.getElementById('scrape-result').style.display='none'" style="color:#4b5563; background:none; border:none; cursor:pointer; font-size:16px; line-height:1; flex-shrink:0;">✕</button>
    </div>
    <div id="new-leads-list" style="margin-top:10px; display:flex; flex-wrap:wrap; gap:6px;"></div>
</div>

{{-- ─── Stats Cards ──────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    {{-- Total Leads --}}
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Leads</span>
            <div class="w-8 h-8 bg-blue-500/10 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>
        <div id="stat-total" class="text-3xl font-bold text-white">{{ number_format($totalLeads) }}</div>
        <div class="text-xs text-gray-500 mt-1">All time</div>
    </div>

    {{-- Today --}}
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Today</span>
            <div class="w-8 h-8 bg-green-500/10 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4v16m8-8H4"/>
                </svg>
            </div>
        </div>
        <div id="stat-today" class="text-3xl font-bold text-white">{{ $leadsToday }}</div>
        <div class="text-xs text-gray-500 mt-1">New leads today</div>
    </div>

    {{-- Hot Leads --}}
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Hot Leads</span>
            <div class="w-8 h-8 bg-red-500/10 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                </svg>
            </div>
        </div>
        <div class="text-3xl font-bold text-white">{{ $hotLeads }}</div>
        <div class="text-xs text-gray-500 mt-1">High-priority leads</div>
    </div>

    {{-- Warm Leads --}}
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Warm Leads</span>
            <div class="w-8 h-8 bg-yellow-500/10 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
        </div>
        <div class="text-3xl font-bold text-white">{{ $warmLeads }}</div>
        <div class="text-xs text-gray-500 mt-1">Medium priority</div>
    </div>
</div>

{{-- ─── Charts & Tables Row ──────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6" style="min-height: 320px;">

    {{-- Country Stats Chart --}}
    <div class="lg:col-span-2 bg-gray-900 rounded-xl border border-gray-800 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-white">Leads by Country</h3>
            <span class="text-xs text-gray-500">{{ $countryStats->count() }} countries</span>
        </div>
        <div style="height: 220px; position: relative;">
            <canvas id="countryChart"></canvas>
        </div>
    </div>

    {{-- Tag Distribution --}}
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-white">Tag Distribution</h3>
        </div>
        <div style="height: 180px; position: relative;">
            <canvas id="tagChart"></canvas>
        </div>
        <div class="mt-4 space-y-2">
            <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span><span class="text-gray-400">Hot</span></div>
                <span class="text-white font-medium">{{ $hotLeads }}</span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-yellow-500 inline-block"></span><span class="text-gray-400">Warm</span></div>
                <span class="text-white font-medium">{{ $warmLeads }}</span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span><span class="text-gray-400">Cold</span></div>
                <span class="text-white font-medium">{{ $coldLeads }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ─── Recent Leads + Activity Log ──────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Recent Leads --}}
    <div class="lg:col-span-2 bg-gray-900 rounded-xl border border-gray-800 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-white">Recent Leads</h3>
            <a href="{{ route('leads.index') }}" class="text-xs text-green-400 hover:text-green-300">View all →</a>
        </div>
        <div class="space-y-3">
            @forelse($recentLeads as $lead)
                <div class="flex items-center gap-3 p-3 bg-gray-800/50 rounded-lg hover:bg-gray-800 transition-colors">
                    <div class="w-9 h-9 rounded-full bg-gray-700 flex items-center justify-center flex-shrink-0 text-sm font-bold text-gray-300">
                        {{ strtoupper(substr($lead->username, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('leads.show', $lead) }}"
                               class="text-sm font-medium text-white hover:text-green-400 transition-colors">
                                @{{ $lead->username }}
                            </a>
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $lead->tag_badge_color }}">
                                {{ ucfirst($lead->tag) }}
                            </span>
                        </div>
                        <div class="text-xs text-gray-500 truncate mt-0.5">{{ Str::limit($lead->bio, 60) }}</div>
                    </div>
                    <div class="text-xs text-gray-600 flex-shrink-0">{{ $lead->country }}</div>
                </div>
            @empty
                <p class="text-sm text-gray-600 text-center py-8">No leads yet. Run the scraper to get started.</p>
            @endforelse
        </div>
    </div>

    {{-- Activity Log --}}
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
        <h3 class="text-sm font-semibold text-white mb-4">Activity Log</h3>
        <div class="space-y-3">
            @forelse($recentLogs as $log)
                <div class="flex gap-3">
                    <div class="w-1.5 h-1.5 rounded-full bg-green-500 mt-1.5 flex-shrink-0"></div>
                    <div>
                        <div class="text-xs text-gray-300">{{ $log->description }}</div>
                        <div class="text-xs text-gray-600 mt-0.5">{{ $log->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            @empty
                <p class="text-xs text-gray-600">No activity yet.</p>
            @endforelse
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ─── Manual Scraper ──────────────────────────────────────────────────────
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

async function runScraper(mode) {
    const btnRun  = document.getElementById('btn-run');
    const btnDry  = document.getElementById('btn-dry');
    const status  = document.getElementById('scrape-status');
    const result  = document.getElementById('scrape-result');
    const msg     = document.getElementById('result-msg');
    const details = document.getElementById('result-details');
    const newList = document.getElementById('new-leads-list');

    // Disable buttons & show loading
    btnRun.disabled = true;
    btnDry.disabled = true;
    btnRun.innerHTML = '<svg style="width:16px;height:16px;animation:spin 1s linear infinite;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Running...';
    status.innerHTML = '<span style="color:#eab308;">⏳ Scraper is running... please wait (1-3 min)</span>';
    result.style.display = 'none';

    const url = mode === 'dry' ? '/scrape/dry-run' : '/scrape/run';

    try {
        const res  = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' }
        });
        const data = await res.json();

        // Update stat cards live
        if (data.total !== undefined) document.getElementById('stat-total').innerText = data.total;
        if (data.today !== undefined) {
            document.getElementById('stat-today').innerText = data.today;
            // Also update header badge
            document.querySelectorAll('[id="today-count"]').forEach(el => el.innerText = data.today + ' leads');
        }

        // Show result box
        result.style.display = 'block';
        result.style.background = data.success ? '#052e16' : '#450a0a';
        result.style.borderColor = data.success ? '#166534' : '#991b1b';
        msg.style.color = data.success ? '#4ade80' : '#f87171';
        msg.innerText = data.message || (data.success ? 'Done!' : 'Error occurred.');
        details.innerText = `Saved: ${data.saved ?? 0}  |  Filtered out: ${data.skipped ?? 0}  |  Duplicates skipped: ${data.duplicates ?? 0}`;

        // Show new lead badges
        newList.innerHTML = '';
        (data.new_leads || []).forEach(lead => {
            const colors = { hot: '#fef2f2;color:#991b1b', warm: '#fefce8;color:#854d0e', cold: '#eff6ff;color:#1e40af' };
            const c = colors[lead.tag] || colors.cold;
            newList.innerHTML += `<a href="/leads" style="background:${c.split(';')[0].replace('background:','').trim()};${c.split(';')[1]};padding:4px 10px;border-radius:999px;font-size:11px;font-weight:600;text-decoration:none;">@${lead.username}</a>`;
        });

        status.innerHTML = `Last run: <span style="color:#22c55e;">✓ ${new Date().toLocaleTimeString()}</span> &nbsp;·&nbsp; Next auto: <span style="color:#d1d5db;">Tonight 8:00 PM</span>`;

    } catch(e) {
        result.style.display = 'block';
        msg.innerText = '❌ Network error: ' + e.message;
    }

    // Re-enable buttons
    btnRun.disabled = false;
    btnDry.disabled = false;
    btnRun.innerHTML = '<svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Start Scraping';
}

// ─── Charts ──────────────────────────────────────────────────────────────
const countryLabels = @json($countryStats->pluck('country'));
const countryData   = @json($countryStats->pluck('total'));

const maxCountry = countryData.length > 0 ? Math.max(...countryData) : 1;

// Country Bar Chart
new Chart(document.getElementById('countryChart'), {
    type: 'bar',
    data: {
        labels: countryLabels.length > 0 ? countryLabels : ['No data yet'],
        datasets: [{
            label: 'Leads',
            data: countryData.length > 0 ? countryData : [0],
            backgroundColor: 'rgba(34, 197, 94, 0.75)',
            borderColor:     'rgba(34, 197, 94, 1)',
            borderWidth: 1,
            borderRadius: 6,
            maxBarThickness: 60,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: {
                grid: { color: 'rgba(255,255,255,0.04)' },
                ticks: { color: '#9ca3af', font: { size: 12 } }
            },
            y: {
                grid: { color: 'rgba(255,255,255,0.04)' },
                ticks: { color: '#9ca3af', stepSize: 1, precision: 0 },
                beginAtZero: true,
                max: maxCountry + Math.max(1, Math.ceil(maxCountry * 0.3)),
            }
        }
    }
});

const totalTags = {{ $hotLeads + $warmLeads + $coldLeads }};

// Tag Doughnut Chart
new Chart(document.getElementById('tagChart'), {
    type: 'doughnut',
    data: {
        labels: ['Hot', 'Warm', 'Cold'],
        datasets: [{
            data: totalTags > 0
                ? [{{ $hotLeads }}, {{ $warmLeads }}, {{ $coldLeads }}]
                : [1, 0, 0],
            backgroundColor: totalTags > 0
                ? ['#ef4444', '#eab308', '#3b82f6']
                : ['#374151', '#374151', '#374151'],
            borderColor: '#111827',
            borderWidth: 3,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '72%',
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.label}: ${totalTags > 0 ? ctx.raw : 0}`
                }
            }
        }
    }
});
</script>
<style>@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}</style>
@endpush
