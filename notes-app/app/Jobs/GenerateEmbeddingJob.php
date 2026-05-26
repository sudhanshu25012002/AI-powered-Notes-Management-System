<?php

namespace App\Jobs;

use App\Models\Note;
use App\Services\EmbeddingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateEmbeddingJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public readonly Note $note)
    {
    }

    /**
     * Execute the job.
     * Generates a Gemini embedding for the note and stores it.
     * Runs synchronously with QUEUE_CONNECTION=sync but keeps
     * the architecture scalable for future async processing.
     */
    public function handle(EmbeddingService $embeddingService): void
    {
        Log::info('GenerateEmbeddingJob: generating embedding', ['note_id' => $this->note->id]);

        $embeddingService->generateAndStore($this->note);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateEmbeddingJob: failed', [
            'note_id' => $this->note->id,
            'error'   => $exception->getMessage(),
        ]);
    }
}
