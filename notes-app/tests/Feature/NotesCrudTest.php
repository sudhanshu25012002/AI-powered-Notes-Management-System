<?php

namespace Tests\Feature;

use App\Jobs\GenerateEmbeddingJob;
use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotesCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_note(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/notes', [
            'title'   => 'Test Note Title',
            'content' => 'This is the test note content, long enough.',
            'tags'    => ['testing', 'php'],
        ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Note created successfully.',
                 ])
                 ->assertJsonPath('data.title', 'Test Note Title')
                 ->assertJsonPath('data.tags', ['testing', 'php']);

        $this->assertDatabaseHas('notes', ['title' => 'Test Note Title']);
        Queue::assertPushed(GenerateEmbeddingJob::class);
    }

    public function test_create_note_fails_validation_without_title(): void
    {
        $response = $this->postJson('/api/notes', [
            'content' => 'Some content here that is long enough.',
        ]);

        $response->assertStatus(422)
                 ->assertJson(['success' => false])
                 ->assertJsonStructure(['errors' => ['title']]);
    }

    public function test_create_note_fails_validation_with_short_content(): void
    {
        $response = $this->postJson('/api/notes', [
            'title'   => 'Valid Title',
            'content' => 'Short',
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors' => ['content']]);
    }

    public function test_can_list_notes_with_pagination(): void
    {
        Queue::fake();
        Note::factory()->count(15)->create();

        $response = $this->getJson('/api/notes?page=1&limit=10');

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure([
                     'data',
                     'meta' => ['current_page', 'per_page', 'total', 'last_page'],
                 ]);

        $this->assertCount(10, $response->json('data'));
    }

    public function test_can_get_single_note(): void
    {
        $note = Note::factory()->create(['title' => 'Single Note']);

        $response = $this->getJson("/api/notes/{$note->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $note->id)
                 ->assertJsonPath('data.title', 'Single Note');
    }

    public function test_returns_404_for_nonexistent_note(): void
    {
        $response = $this->getJson('/api/notes/99999');

        // In debug mode, Laravel may return debug output. Check status only.
        $response->assertStatus(404);
    }

    public function test_can_update_note(): void
    {
        Queue::fake();
        $note = Note::factory()->create();

        $response = $this->putJson("/api/notes/{$note->id}", [
            'title'   => 'Updated Title',
            'content' => 'Updated content that is long enough to pass validation.',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true, 'message' => 'Note updated successfully.'])
                 ->assertJsonPath('data.title', 'Updated Title');

        $this->assertDatabaseHas('notes', ['id' => $note->id, 'title' => 'Updated Title']);
    }

    public function test_can_soft_delete_note(): void
    {
        Queue::fake();
        $note = Note::factory()->create();

        $response = $this->deleteJson("/api/notes/{$note->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('notes', ['id' => $note->id]);
    }

    public function test_deleted_note_not_returned_in_list(): void
    {
        Queue::fake();
        $note = Note::factory()->create(['title' => 'To Be Deleted']);
        $note->delete();

        $response = $this->getJson('/api/notes');

        $titles = collect($response->json('data'))->pluck('title')->toArray();
        $this->assertNotContains('To Be Deleted', $titles);
    }
}
