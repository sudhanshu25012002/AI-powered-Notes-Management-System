<?php

namespace App\Services;

use App\Exceptions\GeminiException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $baseUrl;
    private string $embeddingModel;
    private string $chatModel;

    public function __construct()
    {
        $this->apiKey         = config('services.gemini.api_key');
        $this->baseUrl        = config('services.gemini.base_url');
        $this->embeddingModel = config('services.gemini.embedding_model');
        $this->chatModel      = config('services.gemini.chat_model');
    }

    /**
     * Generate a 768-dimension embedding vector for the given text.
     * Uses Gemini text-embedding-004 model.
     *
     * @return float[]
     * @throws GeminiException
     */
    public function generateEmbedding(string $text): array
    {
        $url = "{$this->baseUrl}/models/{$this->embeddingModel}:embedContent?key={$this->apiKey}";

        Log::info('GeminiService: generating embedding', ['text_length' => strlen($text)]);

        $response = $this->makeRequest($url, [
            'model'   => "models/{$this->embeddingModel}",
            'content' => [
                'parts' => [['text' => $text]],
            ],
        ]);

        return $response['embedding']['values'];
    }

    /**
     * Generate a concise 2-3 sentence summary of the given content.
     * Uses Gemini 1.5 Flash model.
     *
     * @throws GeminiException
     */
    public function generateSummary(string $content): string
    {
        $url = "{$this->baseUrl}/models/{$this->chatModel}:generateContent?key={$this->apiKey}";

        $prompt = "Summarize the following note in a single, short sentence. It must capture only the core message "
                . "and be much shorter than the original text. Do not add any preamble like "
                . "'This note says...' or 'This is about...'. "
                . "Just provide the summary directly.\n\nNote:\n{$content}";

        Log::info('GeminiService: generating summary', ['content_length' => strlen($content)]);

        $response = $this->makeRequest($url, [
            'contents' => [
                ['parts' => [['text' => $prompt]]],
            ],
            'generationConfig' => [
                'maxOutputTokens' => 1024,
                'temperature'     => 0.3,
            ],
        ]);

        return trim($response['candidates'][0]['content']['parts'][0]['text']);
    }

    /**
     * Make a POST request to the Gemini API.
     * Retries once on HTTP 429 (rate limit) after a 2-second delay.
     *
     * @throws GeminiException
     */
    private function makeRequest(string $url, array $payload): array
    {
        $response = Http::timeout(20)->post($url, $payload);

        // Retry once on rate limit
        if ($response->status() === 429) {
            Log::warning('GeminiService: rate limited (429), retrying after 2s');
            sleep(2);
            $response = Http::timeout(20)->post($url, $payload);
        }

        if ($response->failed()) {
            $statusCode = $response->status();
            $body       = $response->body();

            Log::error('GeminiService: API error', [
                'status' => $statusCode,
                'body'   => $body,
            ]);

            throw new GeminiException(
                "Gemini API error {$statusCode}: {$body}",
                $statusCode
            );
        }

        return $response->json();
    }
}
