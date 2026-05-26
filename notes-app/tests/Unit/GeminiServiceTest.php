<?php

namespace Tests\Unit;

use App\Exceptions\GeminiException;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiServiceTest extends TestCase
{
    public function test_generate_embedding_returns_array(): void
    {
        $fakeEmbedding = array_fill(0, 768, 0.1);

        Http::fake([
            '*embedContent*' => Http::response([
                'embedding' => ['values' => $fakeEmbedding],
            ], 200),
        ]);

        $service = new GeminiService();
        $result  = $service->generateEmbedding('Hello world');

        $this->assertIsArray($result);
        $this->assertCount(768, $result);
    }

    public function test_generate_summary_returns_string(): void
    {
        Http::fake([
            '*generateContent*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => 'This is a test summary.']]],
                ]],
            ], 200),
        ]);

        $service = new GeminiService();
        $result  = $service->generateSummary('This is a long note content for testing.');

        $this->assertIsString($result);
        $this->assertEquals('This is a test summary.', $result);
    }

    public function test_throws_gemini_exception_on_api_error(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'Bad Request'], 400),
        ]);

        $this->expectException(GeminiException::class);

        $service = new GeminiService();
        $service->generateEmbedding('test');
    }

    public function test_retries_on_429(): void
    {
        $fakeEmbedding = array_fill(0, 768, 0.5);

        Http::fakeSequence()
            ->push(['error' => 'Rate limited'], 429)
            ->push(['embedding' => ['values' => $fakeEmbedding]], 200);

        $service = new GeminiService();
        $result  = $service->generateEmbedding('test query');

        $this->assertIsArray($result);
        $this->assertCount(768, $result);
    }
}
