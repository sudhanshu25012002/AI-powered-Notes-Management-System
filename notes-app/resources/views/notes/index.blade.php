@extends('layouts.app')

@section('title', 'My Notes')
@section('meta_description', 'Browse and semantically search your AI-powered notes.')

@section('content')
<div x-data="notesApp()" x-cloak>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100">My Notes</h1>
            <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">{{ $notes->total() }} note{{ $notes->total() !== 1 ? 's' : '' }} total</p>
        </div>
    </div>

    {{-- Semantic Search Bar --}}
    <div class="mb-6">
        <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-neutral-500">🔍</span>
            <input
                id="search-input"
                type="text"
                x-model="searchQuery"
                @input.debounce.500ms="search()"
                placeholder="Search notes semantically using AI..."
                class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-neutral-800 rounded-xl text-sm bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500 focus:border-transparent transition"
            />
        </div>
        <div class="mt-2 h-5">
            <p x-show="isSearching" class="text-xs text-gray-400 dark:text-neutral-500">⏳ Searching with AI...</p>
            <p x-show="searchQuery && !isSearching && searchResults !== null" class="text-xs text-gray-500 dark:text-neutral-400">
                <span x-text="searchResults ? searchResults.length : 0"></span> result(s) found for
                "<span x-text="searchQuery" class="font-medium"></span>"
            </p>
            <p x-show="searchError" class="text-xs text-red-500" x-text="searchError"></p>
        </div>
    </div>

    {{-- Search Results --}}
    <template x-if="searchQuery && !isSearching && searchResults !== null">
        <div>
            <template x-if="searchResults.length === 0">
                <div class="text-center py-16 text-gray-400">
                    <p class="text-4xl mb-3">🔎</p>
                    <p class="text-sm">No semantically similar notes found.</p>
                    <p class="text-xs mt-1">Try a different query or create a new note.</p>
                </div>
            </template>

            <div class="grid gap-4">
                <template x-for="note in searchResults" :key="note.id">
                    <div class="bg-white dark:bg-neutral-900/50 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-neutral-800/80 hover:shadow-md dark:hover:border-neutral-700/60 transition cursor-pointer"
                          @click="window.location='/notes/' + note.id">
                        <div class="flex justify-between items-start gap-4">
                            <a :href="'/notes/' + note.id" class="text-base font-semibold text-indigo-600 dark:text-indigo-300 hover:text-indigo-700 dark:hover:text-indigo-200 transition" x-text="note.title"></a>
                            <span class="text-xs bg-green-100 dark:bg-green-950/30 text-green-700 dark:text-green-400 px-2 py-1 rounded-full font-medium shrink-0 border border-transparent dark:border-green-900/30"
                                  x-text="'Score: ' + note.similarity_score"></span>
                        </div>
                        <p class="text-gray-600 dark:text-neutral-300 mt-2 text-sm leading-relaxed line-clamp-2"
                           x-text="note.content.substring(0, 180) + (note.content.length > 180 ? '...' : '')"></p>
                        <div class="mt-3 flex gap-2 flex-wrap">
                            <template x-for="tag in (note.tags || [])">
                                <span class="text-xs bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-300 px-2 py-0.5 rounded-full border border-transparent dark:border-indigo-900/30" x-text="tag"></span>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>

    {{-- Regular Notes List --}}
    <template x-if="!searchQuery">
        <div>
            @if($notes->isEmpty())
                <div class="text-center py-20 text-gray-400">
                    <p class="text-5xl mb-4">📭</p>
                    <p class="text-lg font-medium text-gray-500">No notes yet</p>
                    <p class="text-sm mt-1">Create your first note to get started.</p>
                    <a href="/notes/create" class="inline-block mt-4 bg-indigo-600 text-white text-sm px-5 py-2 rounded-lg hover:bg-indigo-700 transition">
                        + Create Note
                    </a>
            @else
                <div class="grid gap-4">
                    @foreach($notes as $note)
                    <div class="bg-white dark:bg-neutral-900/50 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-neutral-800/80 hover:shadow-md dark:hover:border-neutral-700/60 transition cursor-pointer"
                         onclick="window.location='/notes/{{ $note->id }}'">
                        <div class="flex justify-between items-start gap-4">
                            <a href="/notes/{{ $note->id }}" class="text-base font-semibold text-indigo-600 dark:text-indigo-300 hover:text-indigo-700 dark:hover:text-indigo-200 transition" onclick="event.stopPropagation()">
                                {{ $note->title }}
                            </a>
                            <div class="flex gap-3.5 shrink-0">
                                <a href="/notes/{{ $note->id }}/edit" onclick="event.stopPropagation()" class="text-xs text-gray-400 dark:text-neutral-400 hover:text-indigo-600 dark:hover:text-indigo-300 transition flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    Edit
                                </a>
                                <button
                                    @click.stop="confirmDelete({{ $note->id }}, '{{ addslashes($note->title) }}')"
                                    class="text-xs text-gray-400 dark:text-neutral-400 hover:text-red-500 dark:hover:text-red-400 transition flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Delete
                                </button>
                            </div>
                        </div>
                        <p class="text-gray-600 dark:text-neutral-300 mt-2 text-sm leading-relaxed">{{ Str::limit($note->content, 180) }}</p>
                        <div class="mt-3 flex items-center justify-between">
                            <div class="flex gap-2 flex-wrap">
                                @foreach($note->tags ?? [] as $tag)
                                    <span class="text-xs bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-300 px-2 py-0.5 rounded-full border border-transparent dark:border-indigo-900/30">{{ $tag }}</span>
                                @endforeach
                            </div>
                            <span class="text-xs text-gray-400 dark:text-neutral-500">{{ $note->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $notes->links() }}
                </div>
            @endif
        </div>
    </template>

    {{-- Delete Confirmation Modal --}}
    <div x-show="showDeleteModal"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
        <div class="bg-white dark:bg-neutral-900 border dark:border-neutral-800 rounded-2xl p-6 max-w-sm w-full shadow-xl">
            <h3 class="text-base font-semibold text-gray-900 dark:text-neutral-100 mb-1">Delete Note</h3>
            <p class="text-sm text-gray-500 dark:text-neutral-400 mb-1">Are you sure you want to delete:</p>
            <p class="text-sm font-medium text-gray-800 dark:text-neutral-200 mb-4 truncate" x-text='"\"" + deleteTargetTitle + "\""'></p>
            <p class="text-xs text-gray-400 dark:text-neutral-500 mb-5">This action cannot be undone.</p>
            <div class="flex gap-3 justify-end">
                <button @click="showDeleteModal = false"
                        class="px-4 py-2 text-sm text-gray-600 dark:text-neutral-300 border border-gray-200 dark:border-neutral-800 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-800 transition">
                    Cancel
                </button>
                <button @click="deleteNote()"
                        :disabled="isDeleting"
                        class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition disabled:opacity-50">
                    <span x-show="!isDeleting">Delete</span>
                    <span x-show="isDeleting">Deleting...</span>
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function notesApp() {
    return {
        searchQuery: '',
        searchResults: null,
        isSearching: false,
        searchError: '',
        showDeleteModal: false,
        deleteTargetId: null,
        deleteTargetTitle: '',
        isDeleting: false,

        async search() {
            if (!this.searchQuery.trim()) {
                this.searchResults = null;
                this.searchError = '';
                return;
            }

            this.isSearching = true;
            this.searchError = '';

            try {
                const res = await fetch(`/api/search?q=${encodeURIComponent(this.searchQuery)}`);
                const data = await res.json();

                if (!data.success) {
                    this.searchError = data.message || 'Search failed.';
                    this.searchResults = null;
                } else {
                    this.searchResults = data.data;
                }
            } catch (e) {
                this.searchError = 'Search unavailable. Please try again.';
                this.searchResults = null;
            } finally {
                this.isSearching = false;
            }
        },

        confirmDelete(id, title) {
            this.deleteTargetId = id;
            this.deleteTargetTitle = title;
            this.showDeleteModal = true;
        },

        async deleteNote() {
            this.isDeleting = true;
            try {
                await fetch(`/api/notes/${this.deleteTargetId}`, { method: 'DELETE' });
                this.showDeleteModal = false;
                window.location.reload();
            } catch (e) {
                this.isDeleting = false;
                this.showDeleteModal = false;
            }
        }
    }
}
</script>
@endsection
