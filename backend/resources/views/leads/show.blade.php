@extends('layouts.app')

@section('title', '@' . $lead->username)
@section('page-title', '@' . $lead->username)
@section('page-subtitle', 'Lead detail view')

@section('content')

<div class="max-w-4xl mx-auto">

    {{-- Back button --}}
    <a href="{{ route('leads.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-white mb-5 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to leads
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- ─── Left: Profile Card ─────────────────────────────────────── --}}
        <div class="lg:col-span-1 space-y-4">

            {{-- Profile info --}}
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-green-500 to-blue-600 flex items-center justify-center text-2xl font-bold text-white mb-3">
                        {{ strtoupper(substr($lead->username, 0, 1)) }}
                    </div>

                    {{-- ID badge --}}
                    <div class="text-xs text-gray-600 mb-1">ID #{{ $lead->id }}</div>

                    {{-- Username + Copy button --}}
                    <div class="flex items-center gap-2">
                        <a href="https://www.instagram.com/{{ $lead->username }}/" target="_blank" rel="noopener"
                           class="text-lg font-bold text-white hover:text-green-400 transition-colors">
                            {{ '@' . $lead->username }}
                        </a>
                        <button onclick="copyUsername('{{ $lead->username }}')" title="Copy username"
                                style="padding:3px 6px; background:#1f2937; border:1px solid #374151; border-radius:6px; color:#9ca3af; cursor:pointer; font-size:11px; line-height:1;"
                                onmouseover="this.style.background='#374151';this.style.color='#fff'" onmouseout="this.style.background='#1f2937';this.style.color='#9ca3af'">
                            <svg style="width:12px;height:12px;display:inline;vertical-align:middle;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </div>

                    <a href="https://www.instagram.com/{{ $lead->username }}/" target="_blank" rel="noopener"
                       class="text-xs text-green-400 hover:text-green-300 mt-1 transition-colors flex items-center gap-1">
                        Open Instagram ↗
                    </a>
                </div>

                <div class="mt-5 space-y-3">
                    @if($lead->country)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Country</span>
                            <span class="text-white font-medium">🇧🇷 {{ $lead->country }}</span>
                        </div>
                    @endif

                    @if($lead->gender)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Gender</span>
                            <span class="text-white font-medium capitalize">{{ $lead->gender }}</span>
                        </div>
                    @endif

                    @if($lead->age)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Age</span>
                            <span class="text-white font-medium">{{ $lead->age }} years old</span>
                        </div>
                    @endif

                    @if($lead->job)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Job</span>
                            <span class="text-white font-medium text-xs">{{ $lead->job }}</span>
                        </div>
                    @endif

                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Source</span>
                        <span class="text-white font-medium text-xs">{{ $lead->source_keyword }}</span>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Score</span>
                        <div class="flex items-center gap-2">
                            <div class="w-20 h-1.5 bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $lead->score >= 60 ? 'bg-red-500' : ($lead->score >= 30 ? 'bg-yellow-500' : 'bg-blue-500') }}"
                                     style="width: {{ $lead->score }}%"></div>
                            </div>
                            <span class="text-white font-medium text-xs">{{ $lead->score }}/100</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Added</span>
                        <span class="text-gray-400 text-xs">{{ $lead->created_at->format('M j, Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Tag & Contacted --}}
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-4">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Tag Lead</div>
                <div class="flex gap-2">
                    @foreach(['hot' => '🔥 Hot', 'warm' => '☀️ Warm', 'cold' => '❄️ Cold'] as $tag => $label)
                        <form method="POST" action="{{ route('leads.tag', $lead) }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="tag" value="{{ $tag }}" />
                            <button type="submit"
                                    class="w-full text-xs py-2 rounded-lg font-medium transition-all duration-150
                                           {{ $lead->tag === $tag
                                               ? ($tag === 'hot' ? 'bg-red-600 text-white' : ($tag === 'warm' ? 'bg-yellow-600 text-white' : 'bg-blue-600 text-white'))
                                               : 'bg-gray-800 text-gray-400 hover:bg-gray-700' }}">
                                {{ $label }}
                            </button>
                        </form>
                    @endforeach
                </div>

                <div class="mt-3 pt-3 border-t border-gray-800">
                    <form method="POST" action="{{ route('leads.contacted', $lead) }}">
                        @csrf
                        <button type="submit"
                                class="w-full text-xs py-2 rounded-lg font-medium transition-all duration-150
                                       {{ $lead->is_contacted ? 'bg-green-700 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700' }}">
                            {{ $lead->is_contacted ? '✓ Contacted' : 'Mark as Contacted' }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Delete --}}
            <form method="POST" action="{{ route('leads.destroy', $lead) }}"
                  onsubmit="return confirm('Are you sure you want to delete this lead?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="w-full text-xs py-2.5 rounded-lg bg-red-900/30 text-red-400 hover:bg-red-900/50 transition-colors font-medium">
                    Delete Lead
                </button>
            </form>
        </div>

        {{-- ─── Right: Bio + Notes ─────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Bio --}}
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Instagram Bio</div>
                <p class="text-gray-300 text-sm leading-relaxed whitespace-pre-line">
                    {{ $lead->bio ?: 'No bio available.' }}
                </p>
            </div>

            {{-- Notes --}}
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Notes</div>

                @if (session('success'))
                    <div class="mb-3 text-xs text-green-400">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('leads.notes', $lead) }}">
                    @csrf
                    <textarea name="notes" rows="6"
                              placeholder="Add your notes about this lead..."
                              class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-3 text-sm text-white placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none transition">{{ old('notes', $lead->notes) }}</textarea>
                    <div class="mt-2 flex justify-end">
                        <button type="submit"
                                class="bg-green-600 hover:bg-green-500 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                            Save Notes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function copyUsername(username) {
    navigator.clipboard.writeText('@' + username).then(() => {
        // Show toast
        const toast = document.createElement('div');
        toast.innerText = '@' + username + ' copied!';
        toast.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#22c55e;color:#fff;padding:10px 18px;border-radius:8px;font-size:13px;font-weight:600;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,.3);transition:opacity .3s;';
        document.body.appendChild(toast);
        setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 2000);
    });
}
</script>
@endpush
