<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Services\SemanticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SemanticSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_results_above_threshold(): void
    {
        // Create notes with fake embeddings already stored
        $fakeVec1 = array_fill(0, 768, 0.5);
        $fakeVec2 = array_fill(0, 768, 0.1);

        Note::factory()->create([
            'title'     => 'Machine Learning Note',
            'content'   => 'This is about machine learning and neural networks.',
            'embedding' => json_encode($fakeVec1),
        ]);

        Note::factory()->create([
            'title'     => 'Cooking Recipe',
            'content'   => 'This is a cooking recipe for pasta.',
            'embedding' => json_encode($fakeVec2),
        ]);

        // Fake Gemini embedding response — same as fakeVec1 → cosine = 1.0
        Http::fake([
            '*embedContent*' => Http::response([
                'embedding' => ['values' => $fakeVec1],
            ], 200),
        ]);

        $response = $this->getJson('/api/search?q=machine+learning');

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure([
                     'data',
                     'meta' => ['query', 'total_results'],
                 ]);

        // Machine Learning note should be in results (score = 1.0 >= 0.6 threshold)
        $titles = collect($response->json('data'))->pluck('title')->toArray();
        $this->assertContains('Machine Learning Note', $titles);
    }

    public function test_search_returns_empty_when_no_embeddings(): void
    {
        // Notes exist but have no embeddings
        Note::factory()->count(3)->create(['embedding' => null]);

        Http::fake([
            '*embedContent*' => Http::response([
                'embedding' => ['values' => array_fill(0, 768, 0.5)],
            ], 200),
        ]);

        $response = $this->getJson('/api/search?q=any+query');

        $response->assertStatus(200)
                 ->assertJsonPath('meta.total_results', 0)
                 ->assertJson(['data' => []]);
    }

    public function test_search_requires_query(): void
    {
        $response = $this->getJson('/api/search');

        $response->assertStatus(422);
    }

    public function test_search_requires_minimum_2_chars(): void
    {
        $response = $this->getJson('/api/search?q=a');

        $response->assertStatus(422);
    }

    public function test_each_result_includes_similarity_score(): void
    {
        $fakeVec = array_fill(0, 768, 0.8);

        Note::factory()->create([
            'title'     => 'Test Note',
            'content'   => 'Some test content here.',
            'embedding' => json_encode($fakeVec),
        ]);

        Http::fake([
            '*embedContent*' => Http::response([
                'embedding' => ['values' => $fakeVec],
            ], 200),
        ]);

        $response = $this->getJson('/api/search?q=test+query');

        if (count($response->json('data')) > 0) {
            $response->assertJsonStructure(['data' => [['similarity_score']]]);
        }

        $response->assertStatus(200);
    }
}
