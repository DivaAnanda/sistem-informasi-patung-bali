<?php

namespace App\Models;

use Database\Factories\RegencyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['slug', 'name'])]
#[WithoutTimestamps]
class Regency extends Model
{
    /** @use HasFactory<RegencyFactory> */
    use HasFactory;

    /**
     * @return HasMany<Statue, $this>
     */
    public function statues(): HasMany
    {
        return $this->hasMany(Statue::class);
    }
}
