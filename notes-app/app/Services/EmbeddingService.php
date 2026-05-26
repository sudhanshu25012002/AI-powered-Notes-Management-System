<?php

namespace App\Services;

use App\Exceptions\GeminiException;
use App\Models\Note;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    public function __construct(private GeminiService $gemini)
    {
    }

    /**
     * Generate an embedding for a note and persist it.
     * Combines title + content for a richer semantic representation.
     * Stores as stringified JSON in the LONGTEXT embedding column.
     *
     * Failures are non-fatal — note is still saved, just won't appear
     * in semantic search results until embedding is generated.
     */
    public function generateAndStore(Note $note): void
    {
        // Combine title + content for richer embedding
        $text = $note->title . "\n\n" . $note->content;

        // Truncate to ~8000 chars to stay within token limits
        $text = mb_substr($text, 0, 8000);

        try {
            $embedding = $this->gemini->generateEmbedding($text);

            // Store as stringified JSON in LONGTEXT column
            $note->update(['embedding' => json_encode($embedding)]);

            Log::info('EmbeddingService: embedding stored', ['note_id' => $note->id]);
        } catch (GeminiException $e) {
            // Log but don't fail — note is still usable via keyword search
            Log::warning('EmbeddingService: failed to generate embedding', [
                'note_id' => $note->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
