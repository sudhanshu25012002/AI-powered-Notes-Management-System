<?php

namespace Tests\Feature;

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_endpoint_returns_text(): void
    {
        $note = Note::factory()->create([
            'title'   => 'Test Note',
            'content' => 'This is a long note content about machine learning and AI.',
        ]);

        Http::fake([
            '*generateContent*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => 'This note covers machine learning and AI basics.']]],
                ]],
            ], 200),
        ]);

        $response = $this->postJson("/api/notes/{$note->id}/summary");

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonPath('data.note_id', $note->id)
                 ->assertJsonPath('data.summary', 'This note covers machine learning and AI basics.')
                 ->assertJsonStructure(['data' => ['note_id', 'title', 'summary', 'cached']]);
    }

    public function test_summary_is_cached_on_second_call(): void
    {
        $note = Note::factory()->create([
            'title'   => 'Cached Note',
            'content' => 'Content that will be summarised and cached.',
        ]);

        Http::fake([
            '*generateContent*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => 'Cached summary text.']]],
                ]],
            ], 200),
        ]);

        // First call — not cached
        $first = $this->postJson("/api/notes/{$note->id}/summary");
        $first->assertStatus(200);
        $this->assertFalse($first->json('data.cached'));

        // Second call — should be cached
        $second = $this->postJson("/api/notes/{$note->id}/summary");
        $second->assertStatus(200);
        $this->assertTrue($second->json('data.cached'));
    }

    public function test_summary_returns_404_for_nonexistent_note(): void
    {
        $response = $this->postJson('/api/notes/99999/summary');

        $response->assertStatus(404);
    }

    public function test_summary_cache_invalidated_on_note_update(): void
    {
        // Don't run actual embedding job — use Queue::fake
        \Illuminate\Support\Facades\Queue::fake();

        $note = Note::factory()->create([
            'title'   => 'Cache Test Note',
            'content' => 'Original content for caching test that is long enough.',
        ]);

        // Manually seed the cache
        Cache::put("note_summary_{$note->id}", 'Old summary', 86400);
        $this->assertTrue(Cache::has("note_summary_{$note->id}"));

        // Update the note — NoteService::update() calls Cache::forget before dispatching job
        $this->putJson("/api/notes/{$note->id}", [
            'title'   => 'Updated Title',
            'content' => 'Updated content that is long enough to pass validation rules.',
        ]);

        $this->assertFalse(Cache::has("note_summary_{$note->id}"));
    }
}
