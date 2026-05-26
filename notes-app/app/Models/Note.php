<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Note extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'content',
        'tags',
        'embedding',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * Never expose the embedding vector (768 floats) in API responses.
     */
    protected $hidden = [
        'embedding',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'tags' => 'array',
    ];

    /**
     * Get the embedding as a PHP array.
     * The embedding is stored as a stringified JSON in LONGTEXT column.
     */
    public function getEmbeddingArrayAttribute(): ?array
    {
        if (is_null($this->embedding)) {
            return null;
        }

        return json_decode($this->embedding, true);
    }

    /**
     * Scope: basic keyword search on title and content.
     * Used as fallback when embedding is null.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'LIKE', "%{$term}%")
              ->orWhere('content', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Scope: only notes that have embeddings (eligible for semantic search).
     */
    public function scopeWithEmbedding(Builder $query): Builder
    {
        return $query->whereNotNull('embedding');
    }
}
