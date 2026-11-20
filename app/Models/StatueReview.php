<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * The expert's decision for one statue, keyed by the statue's sheet number so it survives rebuilding the
 * statue database. The statue's name at review time is kept to spot a number later reused for another statue.
 */
#[Connection('reviews')]
#[Fillable(['source_no', 'statue_name', 'statue_type', 'is_iconic', 'notes', 'reviewed_at'])]
class StatueReview extends Model
{
    public const TYPES = ['religius', 'budaya', 'dekoratif'];

    protected function casts(): array
    {
        return [
            'source_no' => 'integer',
            'is_iconic' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    public function isReviewed(): bool
    {
        return $this->statue_type !== null || (bool) $this->is_iconic;
    }

    public function nameChangedFrom(Statue $statue): bool
    {
        return $this->statue_name !== null && $this->statue_name !== $statue->name;
    }
}
