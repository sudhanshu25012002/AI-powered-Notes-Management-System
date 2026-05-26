@extends('layouts.app')

@section('title', 'Edit: ' . $note->title)

@section('content')
<div x-data="editForm()">

    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-400 dark:text-neutral-500 mb-6">
        <a href="/notes" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">Notes</a>
        <span class="mx-2">›</span>
        <a href="/notes/{{ $note->id }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">{{ Str::limit($note->title, 30) }}</a>
        <span class="mx-2">›</span>
        <span class="text-gray-600 dark:text-neutral-300">Edit</span>
    </nav>

    <div class="bg-white dark:bg-neutral-900/50 rounded-2xl shadow-sm border border-gray-100 dark:border-neutral-800/80 p-8 max-w-3xl transition-colors duration-200">
        <h1 class="text-xl font-bold text-gray-900 dark:text-neutral-100 mb-6">Edit Note</h1>

        {{-- Error Banner --}}
        <div x-show="formError" class="mb-5 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 text-red-700 dark:text-red-400 text-sm px-4 py-3 rounded-lg" x-text="formError"></div>

        <form @submit.prevent="submit">

            {{-- Title --}}
            <div class="mb-5">
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Title <span class="text-red-500">*</span></label>
                <input
                    id="title"
                    type="text"
                    x-model="form.title"
                    maxlength="255"
                    class="w-full border border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500 transition"
                    :class="errors.title ? 'border-red-400' : ''"
                />
                <div class="flex justify-between mt-1">
                    <p x-show="errors.title" class="text-xs text-red-500" x-text="errors.title"></p>
                    <p class="text-xs text-gray-400 dark:text-neutral-500 ml-auto" x-text="(form.title || '').length + '/255'"></p>
                </div>
            </div>

            {{-- Content --}}
            <div class="mb-5">
                <label for="content" class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Content <span class="text-red-500">*</span></label>
                <textarea
                    id="content"
                    x-model="form.content"
                    rows="10"
                    class="w-full border border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500 transition resize-y"
                    :class="errors.content ? 'border-red-400' : ''"
                ></textarea>
                <div class="flex justify-between mt-1">
                    <p x-show="errors.content" class="text-xs text-red-500" x-text="errors.content"></p>
                    <p class="text-xs text-gray-400 dark:text-neutral-500 ml-auto" x-text="(form.content || '').length + ' characters'"></p>
                </div>
            </div>

            {{-- Tags --}}
            <div class="mb-7">
                <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Tags <span class="text-gray-400 dark:text-neutral-500 font-normal">(comma-separated)</span></label>
                <input
                    id="tags"
                    type="text"
                    x-model="tagsInput"
                    placeholder="e.g. work, ideas, meeting"
                    class="w-full border border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-gray-900 dark:text-neutral-100 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500 transition"
                />
            </div>

            {{-- Actions --}}
            <div class="flex gap-3">
                <button
                    type="submit"
                    :disabled="isSubmitting"
                    class="bg-indigo-600 text-white text-sm font-medium px-6 py-2.5 rounded-lg hover:bg-indigo-700 transition disabled:opacity-50">
                    <span x-show="!isSubmitting">Save Changes</span>
                    <span x-show="isSubmitting">Saving...</span>
                </button>
                <a href="/notes/{{ $note->id }}" class="text-sm text-gray-500 dark:text-neutral-300 px-4 py-2.5 rounded-lg border border-gray-200 dark:border-neutral-800 hover:bg-gray-50 dark:hover:bg-neutral-800 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function editForm() {
    return {
        form: {
            title:   @json($note->title),
            content: @json($note->content),
        },
        tagsInput: @json(implode(', ', $note->tags ?? [])),
        errors: {},
        formError: '',
        isSubmitting: false,

        parseTags() {
            if (!this.tagsInput.trim()) return [];
            return this.tagsInput.split(',').map(t => t.trim()).filter(t => t.length > 0);
        },

        async submit() {
            this.errors = {};
            this.formError = '';
            this.isSubmitting = true;

            const payload = {
                title:   this.form.title,
                content: this.form.content,
                tags:    this.parseTags(),
            };

            try {
                const res = await fetch('/api/notes/{{ $note->id }}', {
                    method:  'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body:    JSON.stringify(payload),
                });

                const data = await res.json();

                if (res.status === 422) {
                    this.errors = {};
                    if (data.errors) {
                        Object.keys(data.errors).forEach(key => {
                            this.errors[key] = data.errors[key][0];
                        });
                    }
                } else if (data.success) {
                    window.location.href = '/notes/{{ $note->id }}';
                } else {
                    this.formError = data.message || 'Failed to update note.';
                }
            } catch (e) {
                this.formError = 'An unexpected error occurred. Please try again.';
            } finally {
                this.isSubmitting = false;
            }
        }
    }
}
</script>
@endsection
