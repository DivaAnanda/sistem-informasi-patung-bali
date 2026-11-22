<?php

namespace App\Models;

use Database\Factories\ThemeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

/**
 * A theme of a landmark statue, from the informant's taxonomy (20 Nov 2026 interview).
 */
#[Fillable(['slug', 'name', 'description', 'group', 'sort_order'])]
#[Translatable('name', 'description')]
#[WithoutTimestamps]
class Theme extends Model
{
    /** @use HasFactory<ThemeFactory> */
    use HasFactory, HasTranslations;

    /**
     * @return BelongsToMany<Statue, $this>
     */
    public function statues(): BelongsToMany
    {
        return $this->belongsToMany(Statue::class);
    }
}
