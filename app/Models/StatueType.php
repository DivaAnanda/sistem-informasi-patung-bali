<?php

namespace App\Models;

use Database\Factories\StatueTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

#[Fillable(['slug', 'name', 'sort_order'])]
#[Translatable('name')]
#[WithoutTimestamps]
class StatueType extends Model
{
    /** @use HasFactory<StatueTypeFactory> */
    use HasFactory, HasTranslations;

    /**
     * @return HasMany<Statue, $this>
     */
    public function statues(): HasMany
    {
        return $this->hasMany(Statue::class);
    }
}
