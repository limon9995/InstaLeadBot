@extends('layouts.app')

@section('title', 'Leads')
@section('page-title', 'All Leads')
@section('page-subtitle', 'Manage and filter your collected leads')

@section('content')

{{-- ─── Filters ──────────────────────────────────────────────────────────── --}}
<div class="bg-gray-900 rounded-xl border border-gray-800 p-4 mb-4">
    <form method="GET" action="{{ route('leads.index') }}" class="flex flex-wrap gap-3 items-end">

        {{-- Search --}}
        <div class="flex-1 min-w-48">
            <label class="block text-xs text-gray-500 mb-1">Search username</label>
            <div class="relative">
                <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="@username or bio..."
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg pl-9 pr-4 py-2 text-sm text-white placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" />
            </div>
        </div>

        {{-- Country --}}
        <div>
            <label class="block text-xs text-gray-500 mb-1">Country</label>
            <select name="country" class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                <option value="">All countries</option>
                @foreach($countries as $country)
                    <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>
                        {{ $country }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Gender --}}
        <div>
            <label class="block text-xs text-gray-500 mb-1">Gender</label>
            <select name="gender" class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                <option value="">All genders</option>
                <option value="male"    {{ request('gender') == 'male'    ? 'selected' : '' }}>Male</option>
                <option value="female"  {{ request('gender') == 'female'  ? 'selected' : '' }}>Female</option>
                <option value="unknown" {{ request('gender') == 'unknown' ? 'selected' : '' }}>Unknown</option>
            </select>
        </div>

        {{-- Tag --}}
        <div>
            <label class="block text-xs text-gray-500 mb-1">Tag</label>
            <select name="tag" class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                <option value="">All tags</option>
                <option value="hot"  {{ request('tag') == 'hot'  ? 'selected' : '' }}>🔥 Hot</option>
                <option value="warm" {{ request('tag') == 'warm' ? 'selected' : '' }}>☀️ Warm</option>
                <option value="cold" {{ request('tag') == 'cold' ? 'selected' : '' }}>❄️ Cold</option>
            </select>
        </div>

        {{-- Keyword --}}
        <div>
            <label class="block text-xs text-gray-500 mb-1">Keyword</label>
            <select name="keyword" class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                <option value="">All keywords</option>
                @foreach($keywords as $keyword)
                    <option value="{{ $keyword }}" {{ request('keyword') == $keyword ? 'selected' : '' }}>
                        {{ $keyword }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Buttons --}}
        <div class="flex gap-2">
            <button type="submit"
                    class="bg-green-600 hover:bg-green-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                Filter
            </button>
            <a href="{{ route('leads.index') }}"
               class="bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                Reset
            </a>
            <a href="{{ route('leads.export', request()->query()) }}"
               class="bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export
            </a>
        </div>
    </form>
</div>

{{-- ─── Results Info ─────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-3">
    <p class="text-sm text-gray-500">
        Showing <span class="text-white font-medium">{{ $leads->firstItem() ?? 0 }}–{{ $leads->lastItem() ?? 0 }}</span>
        of <span class="text-white font-medium">{{ $leads->total() }}</span> leads
    </p>
</div>

{{-- ─── Table ────────────────────────────────────────────────────────────── --}}
<div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-800/50 border-b border-gray-800">
            <tr>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Username</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Bio</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Country</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Gender</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tag</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Score</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Added</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
            @forelse($leads as $lead)
                <tr class="hover:bg-gray-800/30 transition-colors group">
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $lead->id }}</td>

                    {{-- Username --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            @if($lead->is_contacted)
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 flex-shrink-0" title="Contacted"></span>
                            @else
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-700 flex-shrink-0"></span>
                            @endif
                            <a href="{{ route('leads.show', $lead) }}"
                               class="font-medium text-white hover:text-green-400 transition-colors">
                                @{{ $lead->username }}
                            </a>
                            <a href="{{ $lead->profile_url }}" target="_blank" rel="noopener"
                               class="text-gray-600 hover:text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        </div>
                    </td>

                    {{-- Bio --}}
                    <td class="px-4 py-3 max-w-xs">
                        <span class="text-gray-400 text-xs" title="{{ $lead->bio }}">
                            {{ Str::limit($lead->bio, 55) }}
                        </span>
                    </td>

                    {{-- Country --}}
                    <td class="px-4 py-3">
                        <span class="text-gray-300 text-xs">{{ $lead->country ?? '—' }}</span>
                    </td>

                    {{-- Gender --}}
                    <td class="px-4 py-3">
                        <span class="text-xs text-gray-400 capitalize">{{ $lead->gender ?? '—' }}</span>
                    </td>

                    {{-- Tag --}}
                    <td class="px-4 py-3">
                        <select onchange="updateTag({{ $lead->id }}, this.value)"
                                class="text-xs rounded-full px-2.5 py-1 font-medium border-0 focus:outline-none focus:ring-1 focus:ring-green-500 cursor-pointer
                                       {{ $lead->tag_badge_color }} bg-transparent">
                            <option value="hot"  {{ $lead->tag === 'hot'  ? 'selected' : '' }}>🔥 Hot</option>
                            <option value="warm" {{ $lead->tag === 'warm' ? 'selected' : '' }}>☀️ Warm</option>
                            <option value="cold" {{ $lead->tag === 'cold' ? 'selected' : '' }}>❄️ Cold</option>
                        </select>
                    </td>

                    {{-- Score --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-16 h-1.5 bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $lead->score >= 60 ? 'bg-red-500' : ($lead->score >= 30 ? 'bg-yellow-500' : 'bg-blue-500') }}"
                                     style="width: {{ $lead->score }}%"></div>
                            </div>
                            <span class="text-xs text-gray-500">{{ $lead->score }}</span>
                        </div>
                    </td>

                    {{-- Date --}}
                    <td class="px-4 py-3">
                        <span class="text-xs text-gray-600" title="{{ $lead->created_at }}">
                            {{ $lead->created_at->diffForHumans() }}
                        </span>
                    </td>

                    {{-- Actions --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('leads.show', $lead) }}"
                               class="p-1.5 rounded-lg text-gray-500 hover:text-white hover:bg-gray-700 transition-colors"
                               title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('leads.destroy', $lead) }}" class="inline"
                                  onsubmit="return confirm('Delete @{{ $lead->username }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 rounded-lg text-gray-500 hover:text-red-400 hover:bg-red-900/20 transition-colors"
                                        title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-4 py-16 text-center text-gray-600">
                        No leads found. Adjust filters or run the scraper.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ─── Pagination ───────────────────────────────────────────────────────── --}}
@if ($leads->hasPages())
    <div class="mt-4 flex items-center justify-between text-sm">
        <div class="text-gray-500">Page {{ $leads->currentPage() }} of {{ $leads->lastPage() }}</div>
        <div class="flex gap-1">
            @if ($leads->onFirstPage())
                <span class="px-3 py-1.5 rounded-lg bg-gray-800 text-gray-600 cursor-not-allowed">← Prev</span>
            @else
                <a href="{{ $leads->previousPageUrl() }}"
                   class="px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-300 transition-colors">← Prev</a>
            @endif

            @if ($leads->hasMorePages())
                <a href="{{ $leads->nextPageUrl() }}"
                   class="px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-300 transition-colors">Next →</a>
            @else
                <span class="px-3 py-1.5 rounded-lg bg-gray-800 text-gray-600 cursor-not-allowed">Next →</span>
            @endif
        </div>
    </div>
@endif

@endsection

@push('scripts')
<script>
async function updateTag(id, tag) {
    const token = document.querySelector('meta[name="csrf-token"]').content;
    try {
        const res = await fetch(`/leads/${id}/tag`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ tag })
        });
        if (!res.ok) throw new Error('Failed');
    } catch (e) {
        alert('Failed to update tag. Please try again.');
        location.reload();
    }
}
</script>
@endpush
