<?php

namespace App\Models;

use Database\Factories\StatueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'source_no', 'slug', 'name', 'regency_id', 'district', 'village',
    'latitude', 'longitude', 'statue_type_id', 'proposed_type_id', 'proposed_iconic',
    'junction_type_id', 'junction_arms', 'description', 'sources', 'photos', 'photo_credit',
])]
#[RouteKey('slug')]
#[Translatable('description')]
class Statue extends Model
{
    /** @use HasFactory<StatueFactory> */
    use HasFactory, HasTranslations;

    private const EARTH_RADIUS_KM = 6371;

    protected function casts(): array
    {
        return [
            'source_no' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
            'junction_arms' => 'integer',
            'proposed_iconic' => 'boolean',
            'sources' => 'array',
            'photos' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Regency, $this>
     */
    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    /**
     * @return BelongsTo<StatueType, $this>
     */
    public function statueType(): BelongsTo
    {
        return $this->belongsTo(StatueType::class);
    }

    /**
     * The team's own classification, shown to the expert as a starting point.
     *
     * @return BelongsTo<StatueType, $this>
     */
    public function proposedType(): BelongsTo
    {
        return $this->belongsTo(StatueType::class, 'proposed_type_id');
    }

    /**
     * @return BelongsTo<JunctionType, $this>
     */
    public function junctionType(): BelongsTo
    {
        return $this->belongsTo(JunctionType::class);
    }

    /**
     * @param  array{q?: ?string, kabupaten?: ?string, jenis?: ?string, persimpangan?: ?string}  $filters
     */
    #[Scope]
    protected function filter(Builder $query, array $filters): void
    {
        $query
            ->when($filters['q'] ?? null, fn (Builder $query, string $term) => $query->where(
                fn (Builder $query) => $query
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('district', 'like', "%{$term}%")
                    ->orWhere('village', 'like', "%{$term}%")
            ))
            ->when($filters['kabupaten'] ?? null, fn (Builder $query, string $slug) => $query->whereRelation('regency', 'slug', $slug))
            ->when($filters['jenis'] ?? null, fn (Builder $query, string $slug) => $query->whereRelation('statueType', 'slug', $slug))
            ->when($filters['persimpangan'] ?? null, fn (Builder $query, string $slug) => $query->whereRelation('junctionType', 'slug', $slug));
    }

    public function place(): string
    {
        return collect([$this->village, $this->district, $this->regency?->name])
            ->filter()
            ->unique()
            ->implode(', ');
    }

    public function junctionLabel(): ?string
    {
        if ($this->junctionType === null) {
            return null;
        }

        if (in_array($this->junctionType->slug, JunctionType::WITH_ARMS, true) && $this->junction_arms) {
            return $this->junctionType->name.' · '.__(':count arah', ['count' => $this->junction_arms]);
        }

        return $this->junctionType->name;
    }

    public function hasEnglishDescription(): bool
    {
        return filled($this->getTranslation('description', 'en', false));
    }

    public function proposalLabel(): ?string
    {
        $parts = array_filter([
            $this->proposedType?->name,
            $this->proposed_iconic ? __('Ikonik') : null,
        ]);

        return $parts === [] ? null : implode(' + ', $parts);
    }

    /**
     * @return list<string>
     */
    public function photoUrls(): array
    {
        return array_map(fn (string $file) => asset('images/patung/'.$file), $this->photos ?? []);
    }

    public function photoUrl(): ?string
    {
        return $this->photoUrls()[0] ?? null;
    }

    public function googleMapsUrl(): string
    {
        return 'https://www.google.com/maps/search/?api=1&query='.$this->latitude.','.$this->longitude;
    }

    public function directionsUrl(): string
    {
        return 'https://www.google.com/maps/dir/?api=1&destination='.$this->latitude.','.$this->longitude;
    }

    public function distanceTo(Statue $other): float
    {
        $latDelta = deg2rad($other->latitude - $this->latitude);
        $lngDelta = deg2rad($other->longitude - $this->longitude);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($this->latitude)) * cos(deg2rad($other->latitude)) * sin($lngDelta / 2) ** 2;

        return self::EARTH_RADIUS_KM * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * @return array{name: string, url: string, place: string, lat: float, lng: float}
     */
    public function toMarker(): array
    {
        return [
            'name' => $this->name,
            'url' => route('statues.show', $this),
            'place' => $this->place(),
            'lat' => $this->latitude,
            'lng' => $this->longitude,
        ];
    }
}
