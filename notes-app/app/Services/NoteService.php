<?php

namespace App\Services;

use App\Jobs\GenerateEmbeddingJob;
use App\Models\Note;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class NoteService
{
    /**
     * Get a paginated list of notes (with Redis caching).
     */
    public function list(int $page, int $limit): LengthAwarePaginator
    {
        $key = "notes_page_{$page}_{$limit}";

        // Cache paginator data for 5 minutes
        // We store the raw paginator since we need meta data
        return Cache::remember($key, 300, function () use ($limit) {
            return Note::latest()->paginate($limit);
        });
    }

    /**
     * Create a new note and dispatch embedding generation job.
     */
    public function create(array $data): Note
    {
        $note = Note::create($data);

        // Dispatch embedding job (runs synchronously with QUEUE_CONNECTION=sync)
        GenerateEmbeddingJob::dispatch($note);

        // Invalidate paginated list cache
        $this->invalidateListCache();

        return $note->fresh();
    }

    /**
     * Find a note by ID or throw 404.
     */
    public function findOrFail(int $id): Note
    {
        return Note::findOrFail($id);
    }

    /**
     * Update a note and re-dispatch embedding generation.
     */
    public function update(Note $note, array $data): Note
    {
        $note->update($data);

        // Re-generate embedding since content may have changed
        GenerateEmbeddingJob::dispatch($note->fresh());

        // Invalidate summary cache and list cache
        Cache::forget("note_summary_{$note->id}");
        $this->invalidateListCache();

        return $note->fresh();
    }

    /**
     * Soft delete a note and clear its caches.
     */
    public function delete(Note $note): void
    {
        $note->delete();

        Cache::forget("note_summary_{$note->id}");
        $this->invalidateListCache();
    }

    /**
     * Invalidate all paginated list cache keys.
     *
     * - When CACHE_STORE=redis: uses Redis KEYS pattern matching for bulk delete.
     * - When CACHE_STORE=file/array: falls back to forgetting known page keys.
     */
    private function invalidateListCache(): void
    {
        if (config('cache.default') === 'redis') {
            try {
                $redis  = \Illuminate\Support\Facades\Redis::connection();
                $prefix = config('cache.prefix');
                $keys   = $redis->keys("{$prefix}notes_page_*");
                if (!empty($keys)) {
                    $redis->del($keys);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to invalidate list cache via Redis: ' . $e->getMessage());
            }
        } else {
            // For file/array drivers, forget up to page 20 (covers typical usage)
            for ($p = 1; $p <= 20; $p++) {
                foreach ([5, 10, 15, 20, 25, 50, 100] as $limit) {
                    Cache::forget("notes_page_{$p}_{$limit}");
                }
            }
        }
    }
}
