@extends('layouts.app')

@section('title', 'Leads')
@section('page-title', 'All Leads')
@section('page-subtitle', 'Brazil male crypto/forex leads')

@section('content')

{{-- ── Filters ─────────────────────────────────────────────────────────── --}}
<div style="background:#111827; border:1px solid #1f2937; border-radius:12px; padding:16px; margin-bottom:16px;">
    <form method="GET" action="{{ route('leads.index') }}" style="display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end;">
        <div style="flex:1; min-width:180px;">
            <label style="display:block; font-size:11px; color:#6b7280; margin-bottom:5px;">Search</label>
            <div style="position:relative;">
                <svg style="position:absolute;left:10px;top:9px;width:15px;height:15px;color:#4b5563;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="@username or bio..."
                       style="width:100%; background:#1f2937; border:1px solid #374151; border-radius:8px; padding:8px 12px 8px 32px; font-size:13px; color:#fff; outline:none; box-sizing:border-box;" />
            </div>
        </div>
        <div>
            <label style="display:block; font-size:11px; color:#6b7280; margin-bottom:5px;">Tag</label>
            <select name="tag" style="background:#1f2937; border:1px solid #374151; border-radius:8px; padding:8px 12px; font-size:13px; color:#fff; outline:none;">
                <option value="">All tags</option>
                <option value="hot"  {{ request('tag')=='hot'  ?'selected':'' }}>🔥 Hot</option>
                <option value="warm" {{ request('tag')=='warm' ?'selected':'' }}>☀️ Warm</option>
                <option value="cold" {{ request('tag')=='cold' ?'selected':'' }}>❄️ Cold</option>
            </select>
        </div>
        <div>
            <label style="display:block; font-size:11px; color:#6b7280; margin-bottom:5px;">Keyword</label>
            <select name="keyword" style="background:#1f2937; border:1px solid #374151; border-radius:8px; padding:8px 12px; font-size:13px; color:#fff; outline:none;">
                <option value="">All keywords</option>
                @foreach($keywords as $kw)
                    <option value="{{ $kw }}" {{ request('keyword')==$kw ?'selected':'' }}>{{ $kw }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex; gap:8px;">
            <button type="submit" style="background:#16a34a; color:#fff; font-size:13px; font-weight:600; padding:8px 16px; border-radius:8px; border:none; cursor:pointer;">Filter</button>
            <a href="{{ route('leads.index') }}" style="background:#374151; color:#fff; font-size:13px; font-weight:600; padding:8px 16px; border-radius:8px; text-decoration:none; display:inline-flex; align-items:center;">Reset</a>
            <a href="{{ route('leads.export', request()->query()) }}" style="background:#374151; color:#fff; font-size:13px; font-weight:600; padding:8px 16px; border-radius:8px; text-decoration:none; display:inline-flex; align-items:center; gap:5px;">↓ Export CSV</a>
        </div>
    </form>
</div>

{{-- ── Pending Leads (not contacted) ───────────────────────────────────── --}}
@php
    $pending   = $leads->filter(fn($l) => ! $l->is_contacted);
    $completed = $leads->filter(fn($l) => $l->is_contacted);
@endphp

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
    <div style="font-size:13px; color:#6b7280;">
        🇧🇷 Brazil · Male only &nbsp;·&nbsp;
        <strong style="color:#fff;">{{ $pending->count() }}</strong> pending &nbsp;·&nbsp;
        <strong style="color:#22c55e;">{{ $completed->count() }}</strong> completed
    </div>
    <div style="font-size:12px; color:#4b5563;">
        Total: {{ $leads->total() }} leads
    </div>
</div>

{{-- ── Pending Table ───────────────────────────────────────────────────── --}}
<div style="background:#111827; border:1px solid #1f2937; border-radius:12px; overflow:hidden; margin-bottom:20px;">
    <div style="padding:12px 16px; border-bottom:1px solid #1f2937; display:flex; align-items:center; gap:8px;">
        <span style="width:8px;height:8px;border-radius:50%;background:#f59e0b;display:inline-block;"></span>
        <span style="font-size:13px; font-weight:600; color:#fff;">Pending Leads</span>
        <span style="background:#1f2937; color:#9ca3af; font-size:11px; font-weight:600; padding:2px 8px; border-radius:999px;">{{ $pending->count() }}</span>
    </div>
    <table style="width:100%; border-collapse:collapse; font-size:13px;">
        <thead>
            <tr style="background:#1f2937;">
                <th style="padding:10px 14px; text-align:left; font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; width:40px;">✓</th>
                <th style="padding:10px 14px; text-align:left; font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Username</th>
                <th style="padding:10px 14px; text-align:left; font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Bio</th>
                <th style="padding:10px 14px; text-align:left; font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Age</th>
                <th style="padding:10px 14px; text-align:left; font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Job</th>
                <th style="padding:10px 14px; text-align:left; font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Tag</th>
                <th style="padding:10px 14px; text-align:left; font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Score</th>
                <th style="padding:10px 14px; text-align:right; font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Actions</th>
            </tr>
        </thead>
        <tbody id="pending-tbody">
            @forelse($pending as $lead)
            <tr id="row-{{ $lead->id }}" style="border-bottom:1px solid #1f2937; transition:all .3s;"
                onmouseover="this.style.background='rgba(31,41,55,.5)'" onmouseout="this.style.background='transparent'">

                {{-- Checkbox --}}
                <td style="padding:11px 14px;">
                    <label style="display:flex; align-items:center; cursor:pointer;">
                        <input type="checkbox"
                               onchange="markComplete({{ $lead->id }}, this)"
                               style="width:17px; height:17px; accent-color:#22c55e; cursor:pointer; border-radius:4px;" />
                    </label>
                </td>

                {{-- Username → Instagram --}}
                <td style="padding:11px 14px;">
                    <a href="https://www.instagram.com/{{ $lead->username }}/" target="_blank" rel="noopener"
                       style="color:#fff; font-weight:600; text-decoration:none; display:flex; align-items:center; gap:5px;"
                       onmouseover="this.style.color='#22c55e'" onmouseout="this.style.color='#fff'">
                        {{ '@'.$lead->username }}
                        <svg style="width:11px;height:11px;color:#4b5563;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </td>

                {{-- Bio --}}
                <td style="padding:11px 14px; max-width:210px;">
                    <span style="color:#9ca3af; font-size:12px;" title="{{ $lead->bio }}">{{ Str::limit($lead->bio, 48) }}</span>
                </td>

                {{-- Age --}}
                <td style="padding:11px 14px;">
                    @if($lead->age)
                        <span style="color:#fff; font-weight:600;">{{ $lead->age }}</span><span style="color:#4b5563; font-size:11px;">yr</span>
                    @else
                        <span style="color:#374151;">—</span>
                    @endif
                </td>

                {{-- Job --}}
                <td style="padding:11px 14px; max-width:130px;">
                    <span style="color:#d1d5db; font-size:12px;" title="{{ $lead->job }}">{{ $lead->job ? Str::limit($lead->job, 20) : '—' }}</span>
                </td>

                {{-- Tag --}}
                <td style="padding:11px 14px;">
                    <select onchange="updateTag({{ $lead->id }}, this.value)"
                            style="font-size:11px; font-weight:600; border-radius:999px; padding:3px 10px; border:none; cursor:pointer; outline:none;
                            {{ $lead->tag==='hot' ? 'background:#fef2f2;color:#991b1b;' : ($lead->tag==='warm' ? 'background:#fefce8;color:#854d0e;' : 'background:#eff6ff;color:#1e40af;') }}">
                        <option value="hot"  {{ $lead->tag==='hot'  ?'selected':'' }}>🔥 Hot</option>
                        <option value="warm" {{ $lead->tag==='warm' ?'selected':'' }}>☀️ Warm</option>
                        <option value="cold" {{ $lead->tag==='cold' ?'selected':'' }}>❄️ Cold</option>
                    </select>
                </td>

                {{-- Score --}}
                <td style="padding:11px 14px;">
                    <div style="display:flex; align-items:center; gap:6px;">
                        <div style="width:46px; height:4px; background:#1f2937; border-radius:999px; overflow:hidden;">
                            <div style="height:100%; border-radius:999px; background:{{ $lead->score>=60?'#ef4444':($lead->score>=30?'#eab308':'#3b82f6') }}; width:{{ $lead->score }}%;"></div>
                        </div>
                        <span style="font-size:11px; color:#6b7280;">{{ $lead->score }}</span>
                    </div>
                </td>

                {{-- Actions --}}
                <td style="padding:11px 14px; text-align:right;">
                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:3px;">
                        <a href="{{ route('leads.show', $lead) }}"
                           style="padding:5px; border-radius:6px; color:#6b7280; text-decoration:none; display:inline-flex;"
                           onmouseover="this.style.background='#1f2937';this.style.color='#fff'" onmouseout="this.style.background='transparent';this.style.color='#6b7280'">
                            <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                        <form method="POST" action="{{ route('leads.destroy', $lead) }}" style="display:inline;" onsubmit="return confirm('Delete {{ $lead->username }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="padding:5px; border-radius:6px; border:none; cursor:pointer; background:transparent; color:#6b7280; display:inline-flex;"
                                    onmouseover="this.style.background='rgba(127,29,29,.2)';this.style.color='#f87171'" onmouseout="this.style.background='transparent';this.style.color='#6b7280'">
                                <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="padding:40px; text-align:center; color:#374151; font-size:13px;">
                    All leads completed! 🎉
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if ($leads->hasPages())
<div style="margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; font-size:13px;">
    <span style="color:#6b7280;">Page {{ $leads->currentPage() }} of {{ $leads->lastPage() }}</span>
    <div style="display:flex; gap:6px;">
        @if($leads->onFirstPage())
            <span style="padding:6px 14px; border-radius:8px; background:#1f2937; color:#374151;">← Prev</span>
        @else
            <a href="{{ $leads->previousPageUrl() }}" style="padding:6px 14px; border-radius:8px; background:#1f2937; color:#d1d5db; text-decoration:none;">← Prev</a>
        @endif
        @if($leads->hasMorePages())
            <a href="{{ $leads->nextPageUrl() }}" style="padding:6px 14px; border-radius:8px; background:#1f2937; color:#d1d5db; text-decoration:none;">Next →</a>
        @else
            <span style="padding:6px 14px; border-radius:8px; background:#1f2937; color:#374151;">Next →</span>
        @endif
    </div>
</div>
@endif

{{-- ── Completed Box ───────────────────────────────────────────────────── --}}
<div id="completed-section" style="{{ $completed->isEmpty() ? 'display:none;' : '' }}">
    <div style="background:#0a1f0e; border:1px solid #166534; border-radius:12px; overflow:hidden;">
        <div style="padding:12px 16px; border-bottom:1px solid #166534; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:8px;">
                <span style="font-size:16px;">✅</span>
                <span style="font-size:13px; font-weight:600; color:#4ade80;">Completed Leads</span>
                <span id="completed-count" style="background:#166534; color:#4ade80; font-size:11px; font-weight:700; padding:2px 8px; border-radius:999px;">{{ $completed->count() }}</span>
            </div>
            <button onclick="toggleCompleted()" style="font-size:11px; color:#4b5563; background:none; border:none; cursor:pointer;" id="toggle-completed-btn">
                Hide ▲
            </button>
        </div>

        <div id="completed-body">
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <tbody id="completed-tbody">
                    @foreach($completed as $lead)
                    <tr id="done-{{ $lead->id }}" style="border-bottom:1px solid #0f2e16; opacity:.75;"
                        onmouseover="this.style.background='rgba(22,101,52,.15)';this.style.opacity='1'" onmouseout="this.style.background='transparent';this.style.opacity='.75'">
                        <td style="padding:10px 14px; width:40px;">
                            <label style="display:flex; align-items:center; cursor:pointer;">
                                <input type="checkbox" checked
                                       onchange="markUncomplete({{ $lead->id }}, this)"
                                       style="width:17px; height:17px; accent-color:#22c55e; cursor:pointer;" />
                            </label>
                        </td>
                        <td style="padding:10px 14px;">
                            <a href="https://www.instagram.com/{{ $lead->username }}/" target="_blank" rel="noopener"
                               style="color:#4ade80; font-weight:600; text-decoration:none; display:flex; align-items:center; gap:5px;"
                               onmouseover="this.style.color='#86efac'" onmouseout="this.style.color='#4ade80'">
                                {{ '@'.$lead->username }}
                                <svg style="width:11px;height:11px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        </td>
                        <td style="padding:10px 14px; max-width:240px;">
                            <span style="color:#166534; font-size:12px;" title="{{ $lead->bio }}">{{ Str::limit($lead->bio, 55) }}</span>
                        </td>
                        <td style="padding:10px 14px;">
                            @if($lead->age)<span style="color:#4ade80; font-weight:600;">{{ $lead->age }}</span><span style="color:#166534; font-size:11px;">yr</span>@else<span style="color:#166534;">—</span>@endif
                        </td>
                        <td style="padding:10px 14px; max-width:130px;">
                            <span style="color:#166534; font-size:12px;">{{ $lead->job ? Str::limit($lead->job, 20) : '—' }}</span>
                        </td>
                        <td style="padding:10px 14px;">
                            <span style="font-size:11px; font-weight:600; background:rgba(22,101,52,.3); color:#4ade80; padding:3px 10px; border-radius:999px;">✓ Done</span>
                        </td>
                        <td style="padding:10px 14px;">
                            <span style="font-size:11px; color:#166534;">{{ $lead->created_at->diffForHumans() }}</span>
                        </td>
                        <td style="padding:10px 14px; text-align:right;">
                            <form method="POST" action="{{ route('leads.destroy', $lead) }}" style="display:inline;" onsubmit="return confirm('Delete {{ $lead->username }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="padding:5px; border-radius:6px; border:none; cursor:pointer; background:transparent; color:#166534; display:inline-flex;"
                                        onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='#166534'">
                                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// ── Check → move to Completed ─────────────────────────────────────────────
async function markComplete(id, checkbox) {
    checkbox.disabled = true;

    const res  = await fetch(`/leads/${id}/contacted`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' }
    });
    const data = await res.json();

    if (data.is_contacted) {
        const row = document.getElementById('row-' + id);
        if (!row) return;

        // Animate out
        row.style.transition = 'opacity .4s, transform .4s';
        row.style.opacity    = '0';
        row.style.transform  = 'translateX(20px)';

        setTimeout(() => {
            row.remove();
            addToCompleted(id, data);
            updateCounts();
        }, 400);
    } else {
        checkbox.checked  = false;
        checkbox.disabled = false;
    }
}

// ── Uncheck → move back to Pending ───────────────────────────────────────
async function markUncomplete(id, checkbox) {
    checkbox.disabled = true;

    await fetch(`/leads/${id}/contacted`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' }
    });

    // Reload to refresh both tables properly
    location.reload();
}

// ── Dynamically add row to completed tbody ────────────────────────────────
function addToCompleted(id, data) {
    const section = document.getElementById('completed-section');
    const tbody   = document.getElementById('completed-tbody');
    section.style.display = '';

    const tr = document.createElement('tr');
    tr.id    = 'done-' + id;
    tr.style.cssText = 'border-bottom:1px solid #0f2e16; opacity:0; transition:opacity .4s;';
    tr.innerHTML = `
        <td style="padding:10px 14px; width:40px;">
            <label style="display:flex;align-items:center;cursor:pointer;">
                <input type="checkbox" checked onchange="markUncomplete(${id}, this)"
                       style="width:17px;height:17px;accent-color:#22c55e;cursor:pointer;" />
            </label>
        </td>
        <td style="padding:10px 14px;">
            <a href="https://www.instagram.com/${data.username}/" target="_blank"
               style="color:#4ade80;font-weight:600;text-decoration:none;">
                @${data.username}
            </a>
        </td>
        <td colspan="4" style="padding:10px 14px;">
            <span style="font-size:11px;font-weight:600;background:rgba(22,101,52,.3);color:#4ade80;padding:3px 10px;border-radius:999px;">✓ Done</span>
        </td>
        <td colspan="2"></td>
    `;
    tbody.prepend(tr);
    setTimeout(() => tr.style.opacity = '.75', 50);
}

// ── Update pending/completed counters ─────────────────────────────────────
function updateCounts() {
    const pendingRows   = document.querySelectorAll('#pending-tbody tr[id^="row-"]').length;
    const completedRows = document.querySelectorAll('#completed-tbody tr[id^="done-"]').length;
    const countEl = document.getElementById('completed-count');
    if (countEl) countEl.innerText = completedRows;
}

// ── Toggle completed section ──────────────────────────────────────────────
function toggleCompleted() {
    const body = document.getElementById('completed-body');
    const btn  = document.getElementById('toggle-completed-btn');
    if (body.style.display === 'none') {
        body.style.display = '';
        btn.innerText = 'Hide ▲';
    } else {
        body.style.display = 'none';
        btn.innerText = 'Show ▼';
    }
}

// ── Tag update ───────────────────────────────────────────────────────────
async function updateTag(id, tag) {
    await fetch(`/leads/${id}/tag`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ tag })
    });
}
</script>
@endpush
