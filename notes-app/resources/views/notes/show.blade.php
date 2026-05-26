@extends('layouts.app')

@section('title', $note->title)
@section('meta_description', Str::limit($note->content, 160))

@section('content')
<div x-data="summaryApp()">

    {{-- Breadcrumb --}}
    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-400 dark:text-neutral-500 mb-6">
        <a href="/notes" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">Notes</a>
        <span class="mx-2">›</span>
        <span class="text-gray-600 dark:text-neutral-300">{{ Str::limit($note->title, 40) }}</span>
    </nav>

    <div class="bg-white dark:bg-neutral-900/50 rounded-2xl shadow-sm border border-gray-100 dark:border-neutral-800/80 p-8 transition-colors duration-200">

        {{-- Header --}}
        <div class="flex justify-between items-start gap-4 mb-6">
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100">{{ $note->title }}</h1>
                <p class="text-xs text-gray-400 dark:text-neutral-500 mt-2">
                    Created {{ $note->created_at->format('d M Y, h:i A') }}
                    @if($note->updated_at != $note->created_at)
                        · Updated {{ $note->updated_at->diffForHumans() }}
                    @endif
                </p>
            </div>
            <a href="/notes/{{ $note->id }}/edit"
               class="shrink-0 bg-indigo-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                Edit Note
            </a>
        </div>

        {{-- Tags --}}
        @if($note->tags && count($note->tags) > 0)
        <div class="flex gap-2 flex-wrap mb-6">
            @foreach($note->tags as $tag)
                <span class="text-xs bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-300 px-3 py-1 rounded-full font-medium">{{ $tag }}</span>
            @endforeach
        </div>
        @endif

        {{-- Content --}}
        <div class="text-gray-700 dark:text-neutral-300 text-sm leading-relaxed whitespace-pre-wrap mb-8 border-b border-gray-100 dark:border-neutral-800/80 pb-8">{{ $note->content }}</div>

        {{-- AI Summary Section --}}
        <div>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="text-lg">✨</span>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-neutral-200">AI Summary</h2>
                    <span class="text-xs text-gray-400 dark:text-neutral-500">· powered by Gemini</span>
                </div>
                <button
                    id="generate-summary-btn"
                    @click="generateSummary({{ $note->id }})"
                    :disabled="isLoading"
                    class="bg-purple-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-purple-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <span x-show="!isLoading">Generate Summary</span>
                    <span x-show="isLoading" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Generating...
                    </span>
                </button>
            </div>

            {{-- Summary Result --}}
            <div x-show="summary"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="bg-purple-50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-900/50 rounded-xl p-5">
                <div class="flex items-start gap-3">
                    <span class="text-purple-500 mt-0.5">✨</span>
                    <div>
                        <p class="text-gray-700 dark:text-neutral-200 text-sm leading-relaxed" x-text="summary"></p>
                        <p x-show="isCached" class="text-xs text-purple-400 dark:text-purple-300 mt-2">⚡ Served from cache</p>
                    </div>
                </div>
            </div>

            {{-- Error State --}}
            <div x-show="error"
                 class="bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-xl p-4">
                <p class="text-red-600 dark:text-red-400 text-sm" x-text="error"></p>
            </div>

            {{-- Initial hint --}}
            <div x-show="!summary && !error && !isLoading" class="bg-gray-50 dark:bg-neutral-900/30 border border-dashed border-gray-200 dark:border-neutral-800/80 rounded-xl p-5 text-center">
                <p class="text-gray-400 dark:text-neutral-500 text-sm">Click "Generate Summary" to get an AI-powered summary of this note.</p>
            </div>
        </div>
    </div>

    {{-- Back link --}}
    <div class="mt-6">
        <a href="/notes" class="text-sm text-gray-400 dark:text-neutral-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition">← Back to Notes</a>
    </div>
</div>

<script>
function summaryApp() {
    return {
        summary: '',
        isCached: false,
        isLoading: false,
        error: '',

        async generateSummary(noteId) {
            this.isLoading = true;
            this.error = '';
            this.summary = '';

            try {
                const res = await fetch(`/api/notes/${noteId}/summary`, { method: 'POST' });
                const data = await res.json();

                if (!data.success) {
                    this.error = data.message || 'Failed to generate summary.';
                } else {
                    this.summary = data.data.summary;
                    this.isCached = data.data.cached;
                }
            } catch (e) {
                this.error = 'Failed to generate summary. Please try again.';
            } finally {
                this.isLoading = false;
            }
        }
    }
}
</script>
@endsection
