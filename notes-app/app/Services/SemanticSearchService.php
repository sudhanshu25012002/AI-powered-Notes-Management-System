<?php

namespace App\Services;

use App\Helpers\VectorMath;
use App\Models\Note;
use Illuminate\Support\Facades\Log;

class SemanticSearchService
{
    private const SIMILARITY_THRESHOLD = 0.45;
    private const MAX_RESULTS          = 10;

    public function __construct(private GeminiService $gemini)
    {
    }

    /**
     * Perform semantic search across all embedded notes.
     *
     * Flow:
     * 1. Generate query embedding via Gemini
     * 2. Load all notes with embeddings
     * 3. Compute cosine similarity for each note
     * 4. Filter by threshold, sort by score, return top N
     *
     * Performance note: O(n) full scan — acceptable at demo scale.
     * Production would use pgvector or a dedicated vector DB.
     *
     * @return array<int, array{id: int, title: string, content: string, tags: array, similarity_score: float}>
     */
    public function search(string $query): array
    {
        Log::info('SemanticSearchService: search started', ['query' => $query]);

        // Step 1: Embed the query
        $queryEmbedding = $this->gemini->generateEmbedding($query);

        // Step 2: Load all notes that have embeddings
        $notes = Note::withEmbedding()->get();

        if ($notes->isEmpty()) {
            Log::info('SemanticSearchService: no embedded notes found');
            return [];
        }

        // Step 3: Score each note
        $scored = $notes
            ->map(function (Note $note) use ($queryEmbedding) {
                $noteEmbedding = json_decode($note->embedding, true);

                if (!is_array($noteEmbedding)) {
                    return null;
                }

                $score = VectorMath::cosineSimilarity($queryEmbedding, $noteEmbedding);

                return [
                    'note'             => $note,
                    'similarity_score' => round($score, 4),
                ];
            })
            ->filter(fn ($item) => $item !== null && $item['similarity_score'] >= self::SIMILARITY_THRESHOLD)
            ->sortByDesc('similarity_score')
            ->take(self::MAX_RESULTS)
            ->values();

        Log::info('SemanticSearchService: search complete', ['results' => $scored->count()]);

        // Step 4: Format output — merge note data with similarity score
        return $scored->map(function ($item) {
            $note = $item['note'];
            return [
                'id'               => $note->id,
                'title'            => $note->title,
                'content'          => $note->content,
                'tags'             => $note->tags ?? [],
                'created_at'       => $note->created_at?->toISOString(),
                'updated_at'       => $note->updated_at?->toISOString(),
                'similarity_score' => $item['similarity_score'],
            ];
        })->toArray();
    }
}
