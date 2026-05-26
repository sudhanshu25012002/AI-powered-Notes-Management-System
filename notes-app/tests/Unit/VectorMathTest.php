<?php

namespace Tests\Unit;

use App\Helpers\VectorMath;
use Tests\TestCase;

class VectorMathTest extends TestCase
{
    public function test_identical_vectors_return_one(): void
    {
        $vec = [1.0, 2.0, 3.0];
        $this->assertEqualsWithDelta(1.0, VectorMath::cosineSimilarity($vec, $vec), 0.0001);
    }

    public function test_opposite_vectors_return_negative_one(): void
    {
        $a = [1.0, 0.0, 0.0];
        $b = [-1.0, 0.0, 0.0];
        $this->assertEqualsWithDelta(-1.0, VectorMath::cosineSimilarity($a, $b), 0.0001);
    }

    public function test_orthogonal_vectors_return_zero(): void
    {
        $a = [1.0, 0.0, 0.0];
        $b = [0.0, 1.0, 0.0];
        $this->assertEqualsWithDelta(0.0, VectorMath::cosineSimilarity($a, $b), 0.0001);
    }

    public function test_similar_vectors_return_high_score(): void
    {
        $a = [1.0, 2.0, 3.0];
        $b = [1.1, 2.1, 3.1];
        $score = VectorMath::cosineSimilarity($a, $b);
        $this->assertGreaterThan(0.99, $score);
    }

    public function test_zero_vector_returns_zero(): void
    {
        $a = [0.0, 0.0, 0.0];
        $b = [1.0, 2.0, 3.0];
        $this->assertEquals(0.0, VectorMath::cosineSimilarity($a, $b));
    }

    public function test_throws_on_mismatched_lengths(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        VectorMath::cosineSimilarity([1.0, 2.0], [1.0, 2.0, 3.0]);
    }
}
