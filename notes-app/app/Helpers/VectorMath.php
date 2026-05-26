<?php

namespace App\Helpers;

class VectorMath
{
    /**
     * Compute cosine similarity between two vectors.
     *
     * Formula: similarity = (A · B) / (||A|| × ||B||)
     *
     * Returns a float between -1 (opposite) and 1 (identical).
     * For Gemini text embeddings, scores >= 0.60 indicate meaningful similarity.
     *
     * @param  float[] $a
     * @param  float[] $b
     * @throws \InvalidArgumentException if vectors have different lengths
     */
    public static function cosineSimilarity(array $a, array $b): float
    {
        if (count($a) !== count($b)) {
            throw new \InvalidArgumentException(
                'Vectors must be the same length. Got ' . count($a) . ' and ' . count($b) . '.'
            );
        }

        $dot   = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($a as $i => $val) {
            $dot   += $val * $b[$i];
            $normA += $val * $val;
            $normB += $b[$i] * $b[$i];
        }

        $denominator = sqrt($normA) * sqrt($normB);

        if ($denominator == 0.0) {
            return 0.0;
        }

        return $dot / $denominator;
    }
}
