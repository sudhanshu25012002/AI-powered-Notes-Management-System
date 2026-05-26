<?php

namespace Database\Factories;

use App\Models\Note;
use Illuminate\Database\Eloquent\Factories\Factory;

class NoteFactory extends Factory
{
    protected $model = Note::class;

    public function definition(): array
    {
        return [
            'title'     => $this->faker->sentence(4),
            'content'   => $this->faker->paragraphs(3, true),
            'tags'      => $this->faker->randomElements(['work', 'ideas', 'personal', 'tech', 'health'], 2),
            'embedding' => null,
        ];
    }

    /**
     * State: note with a fake embedding (for search tests).
     */
    public function withEmbedding(): static
    {
        return $this->state(fn () => [
            'embedding' => json_encode(array_fill(0, 768, round($this->faker->randomFloat(4, -1, 1), 4))),
        ]);
    }
}
