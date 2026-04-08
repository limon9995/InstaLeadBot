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
                    <h2 class="text-lg font-bold text-white">@{{ $lead->username }}</h2>
                    <a href="{{ $lead->profile_url }}" target="_blank" rel="noopener"
                       class="text-xs text-green-400 hover:text-green-300 mt-1 transition-colors">
                        View on Instagram ↗
                    </a>
                </div>

                <div class="mt-5 space-y-3">
                    @if($lead->country)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Country</span>
                            <span class="text-white font-medium">{{ $lead->country }}</span>
                        </div>
                    @endif

                    @if($lead->gender)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Gender</span>
                            <span class="text-white font-medium capitalize">{{ $lead->gender }}</span>
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
