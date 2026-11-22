<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * The informant's themes for one statue, keyed by the statue's sheet number so they survive rebuilding the
 * statue database. The statue's name at review time is kept to spot a number later reused for another statue.
 */
#[Connection('reviews')]
#[Fillable(['source_no', 'statue_name', 'themes', 'notes', 'reviewed_at'])]
class StatueReview extends Model
{
    protected function casts(): array
    {
        return [
            'source_no' => 'integer',
            'themes' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function isReviewed(): bool
    {
        return filled($this->themes);
    }

    public function nameChangedFrom(Statue $statue): bool
    {
        return $this->statue_name !== null && $this->statue_name !== $statue->name;
    }
}
