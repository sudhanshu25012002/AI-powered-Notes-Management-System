<?php

namespace App\Services;

use App\Exceptions\GeminiException;
use App\Models\Note;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SummaryService
{
    public function __construct(private GeminiService $gemini)
    {
    }

    /**
     * Get or generate an AI summary for a note.
     * Summary is cached for 24 hours (86400 seconds).
     * Cache is invalidated when note is updated or deleted (via NoteService).
     *
     * @throws GeminiException
     */
    public function getSummary(Note $note): array
    {
        $cacheKey = "note_summary_{$note->id}";
        $cached   = Cache::has($cacheKey);

        $summary = Cache::remember($cacheKey, 86400, function () use ($note) {
            Log::info('SummaryService: generating new summary', ['note_id' => $note->id]);
            return $this->gemini->generateSummary($note->content);
        });

        return [
            'note_id' => $note->id,
            'title'   => $note->title,
            'summary' => $summary,
            'cached'  => $cached,
        ];
    }
}
