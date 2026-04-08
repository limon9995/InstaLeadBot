@extends('layouts.app')

@section('title', 'Leads')
@section('page-title', 'All Leads')
@section('page-subtitle', 'Brazil male crypto/forex leads')

@section('content')

{{-- ── Filters ────────────────────────────────────────────────────────── --}}
<div style="background:#111827; border:1px solid #1f2937; border-radius:12px; padding:16px; margin-bottom:16px;">
    <form method="GET" action="{{ route('leads.index') }}" style="display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end;">

        {{-- Search --}}
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

        {{-- Tag --}}
        <div>
            <label style="display:block; font-size:11px; color:#6b7280; margin-bottom:5px;">Tag</label>
            <select name="tag" style="background:#1f2937; border:1px solid #374151; border-radius:8px; padding:8px 12px; font-size:13px; color:#fff; outline:none;">
                <option value="">All tags</option>
                <option value="hot"  {{ request('tag')=='hot'  ?'selected':'' }}>🔥 Hot</option>
                <option value="warm" {{ request('tag')=='warm' ?'selected':'' }}>☀️ Warm</option>
                <option value="cold" {{ request('tag')=='cold' ?'selected':'' }}>❄️ Cold</option>
            </select>
        </div>

        {{-- Keyword --}}
        <div>
            <label style="display:block; font-size:11px; color:#6b7280; margin-bottom:5px;">Keyword</label>
            <select name="keyword" style="background:#1f2937; border:1px solid #374151; border-radius:8px; padding:8px 12px; font-size:13px; color:#fff; outline:none;">
                <option value="">All keywords</option>
                @foreach($keywords as $kw)
                    <option value="{{ $kw }}" {{ request('keyword')==$kw ?'selected':'' }}>{{ $kw }}</option>
                @endforeach
            </select>
        </div>

        {{-- Buttons --}}
        <div style="display:flex; gap:8px;">
            <button type="submit" style="background:#16a34a; color:#fff; font-size:13px; font-weight:600; padding:8px 16px; border-radius:8px; border:none; cursor:pointer;">Filter</button>
            <a href="{{ route('leads.index') }}" style="background:#374151; color:#fff; font-size:13px; font-weight:600; padding:8px 16px; border-radius:8px; text-decoration:none; display:inline-flex; align-items:center;">Reset</a>
            <a href="{{ route('leads.export', request()->query()) }}" style="background:#374151; color:#fff; font-size:13px; font-weight:600; padding:8px 16px; border-radius:8px; text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
                ↓ Export CSV
            </a>
        </div>
    </form>
</div>

{{-- Count --}}
<div style="font-size:13px; color:#6b7280; margin-bottom:10px;">
    Showing <strong style="color:#fff;">{{ $leads->firstItem() ?? 0 }}–{{ $leads->lastItem() ?? 0 }}</strong>
    of <strong style="color:#fff;">{{ $leads->total() }}</strong> leads &nbsp;🇧🇷 Brazil · Male only
</div>

{{-- ── Table ───────────────────────────────────────────────────────────── --}}
<div style="background:#111827; border:1px solid #1f2937; border-radius:12px; overflow:hidden;">
    <table style="width:100%; border-collapse:collapse; font-size:13px;">
        <thead>
            <tr style="background:#1f2937; border-bottom:1px solid #374151;">
                <th style="text-align:left; padding:11px 14px; font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">#</th>
                <th style="text-align:left; padding:11px 14px; font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Username</th>
                <th style="text-align:left; padding:11px 14px; font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Bio</th>
                <th style="text-align:left; padding:11px 14px; font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Age</th>
                <th style="text-align:left; padding:11px 14px; font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Job</th>
                <th style="text-align:left; padding:11px 14px; font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Tag</th>
                <th style="text-align:left; padding:11px 14px; font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Score</th>
                <th style="text-align:left; padding:11px 14px; font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Added</th>
                <th style="text-align:right; padding:11px 14px; font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leads as $lead)
            <tr style="border-bottom:1px solid #1f2937;" onmouseover="this.style.background='rgba(31,41,55,.5)'" onmouseout="this.style.background='transparent'">

                {{-- ID --}}
                <td style="padding:11px 14px; color:#4b5563;">{{ $lead->id }}</td>

                {{-- Username → opens Instagram --}}
                <td style="padding:11px 14px;">
                    <div style="display:flex; align-items:center; gap:6px;">
                        @if($lead->is_contacted)
                            <span style="width:6px;height:6px;border-radius:50%;background:#22c55e;flex-shrink:0;" title="Contacted"></span>
                        @endif
                        <a href="https://www.instagram.com/{{ $lead->username }}/" target="_blank" rel="noopener"
                           style="color:#fff; font-weight:600; text-decoration:none; display:flex; align-items:center; gap:5px;"
                           onmouseover="this.style.color='#22c55e'" onmouseout="this.style.color='#fff'">
                            {{ '@'.$lead->username }}
                            <svg style="width:12px;height:12px;color:#4b5563;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    </div>
                </td>

                {{-- Bio --}}
                <td style="padding:11px 14px; max-width:220px;">
                    <span style="color:#9ca3af; font-size:12px;" title="{{ $lead->bio }}">
                        {{ Str::limit($lead->bio, 50) }}
                    </span>
                </td>

                {{-- Age --}}
                <td style="padding:11px 14px;">
                    @if($lead->age)
                        <span style="color:#fff; font-weight:600;">{{ $lead->age }}</span>
                        <span style="color:#4b5563; font-size:11px;">yr</span>
                    @else
                        <span style="color:#374151;">—</span>
                    @endif
                </td>

                {{-- Job --}}
                <td style="padding:11px 14px; max-width:140px;">
                    <span style="color:#d1d5db; font-size:12px;" title="{{ $lead->job }}">
                        {{ $lead->job ? Str::limit($lead->job, 22) : '—' }}
                    </span>
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
                    <div style="display:flex; align-items:center; gap:7px;">
                        <div style="width:50px; height:5px; background:#1f2937; border-radius:999px; overflow:hidden;">
                            <div style="height:100%; border-radius:999px; background:{{ $lead->score>=60?'#ef4444':($lead->score>=30?'#eab308':'#3b82f6') }}; width:{{ $lead->score }}%;"></div>
                        </div>
                        <span style="font-size:11px; color:#6b7280;">{{ $lead->score }}</span>
                    </div>
                </td>

                {{-- Date --}}
                <td style="padding:11px 14px;">
                    <span style="font-size:11px; color:#4b5563;" title="{{ $lead->created_at }}">
                        {{ $lead->created_at->diffForHumans() }}
                    </span>
                </td>

                {{-- Actions --}}
                <td style="padding:11px 14px; text-align:right;">
                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:4px;">
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
                <td colspan="9" style="padding:60px; text-align:center; color:#374151;">
                    No Brazil leads yet. Run: <code style="background:#1f2937; padding:2px 8px; border-radius:4px;">php artisan leadbot:scrape --dry-run</code>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if ($leads->hasPages())
<div style="margin-top:14px; display:flex; align-items:center; justify-content:space-between; font-size:13px;">
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

@endsection

@push('scripts')
<script>
async function updateTag(id, tag) {
    const token = document.querySelector('meta[name="csrf-token"]').content;
    await fetch(`/leads/${id}/tag`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
        body: JSON.stringify({ tag })
    });
}
</script>
@endpush
