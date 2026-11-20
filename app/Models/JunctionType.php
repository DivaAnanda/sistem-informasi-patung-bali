<?php

namespace App\Models;

use Database\Factories\JunctionTypeFactory;
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
class JunctionType extends Model
{
    /** @use HasFactory<JunctionTypeFactory> */
    use HasFactory, HasTranslations;

    // Junctions whose number of arms varies, so the label shows it.
    public const WITH_ARMS = ['bundaran', 'simpang'];

    /**
     * @return HasMany<Statue, $this>
     */
    public function statues(): HasMany
    {
        return $this->hasMany(Statue::class);
    }
}
