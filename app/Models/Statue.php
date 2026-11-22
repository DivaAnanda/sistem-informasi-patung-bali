<?php

namespace App\Models;

use App\Support\StatuePhotos;
use Database\Factories\StatueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'source_no', 'slug', 'name', 'regency_id', 'district', 'village',
    'latitude', 'longitude', 'proposed_themes', 'proposal_note',
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
            'proposed_themes' => 'array',
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
     * Themes confirmed by the informant; empty until the classification is final.
     *
     * @return BelongsToMany<Theme, $this>
     */
    public function themes(): BelongsToMany
    {
        return $this->belongsToMany(Theme::class)->orderBy('sort_order');
    }

    /**
     * @return BelongsTo<JunctionType, $this>
     */
    public function junctionType(): BelongsTo
    {
        return $this->belongsTo(JunctionType::class);
    }

    /**
     * @param  array{q?: ?string, kabupaten?: ?string, tema?: ?string, persimpangan?: ?string}  $filters
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
            ->when($filters['tema'] ?? null, fn (Builder $query, string $slug) => $query->whereRelation('themes', 'slug', $slug))
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

    /**
     * The team's suggested themes, shown to the informant as a starting point.
     *
     * @param  Collection<int, Theme>|null  $catalog  all themes, passed in by callers that list many statues
     * @return Collection<int, Theme>
     */
    public function proposedThemes(?Collection $catalog = null): Collection
    {
        $catalog ??= Theme::query()->orderBy('sort_order')->get();

        return $catalog->whereIn('slug', $this->proposed_themes ?? [])->values();
    }

    /**
     * @param  Collection<int, Theme>|null  $catalog
     */
    public function proposalLabel(?Collection $catalog = null): ?string
    {
        $names = $this->proposedThemes($catalog)->map(fn (Theme $theme) => $theme->name);

        return $names->isEmpty() ? null : $names->implode(' + ');
    }

    /**
     * Who guards this kind of junction according to Tutur Gong Besi, as explained by the informant:
     * Sang Hyang Catur Bhuwana at a crossroads, Sang Hyang Sapuh Jagat at a three-way junction.
     * Offerings at the junction are for this guardian, not for the statue.
     */
    public function junctionGuardian(): ?string
    {
        $slug = $this->junctionType?->slug;
        $arms = $this->junction_arms;

        return match (true) {
            $slug === 'perempatan', $slug === 'bundaran' && $arms === 4 => 'Sang Hyang Catur Bhuwana',
            $slug === 'pertigaan', $slug === 'bundaran' && $arms === 3 => 'Sang Hyang Sapuh Jagat',
            default => null,
        };
    }

    /**
     * @return list<string>
     */
    public function photoUrls(): array
    {
        return array_map(StatuePhotos::url(...), $this->photos ?? []);
    }

    /**
     * @return list<string>
     */
    public function thumbnailUrls(): array
    {
        return array_map(StatuePhotos::thumbnailUrl(...), $this->photos ?? []);
    }

    public function photoUrl(): ?string
    {
        return $this->photoUrls()[0] ?? null;
    }

    public function thumbnailUrl(): ?string
    {
        return isset($this->photos[0]) ? StatuePhotos::thumbnailUrl($this->photos[0]) : null;
    }

    /**
     * Opens the nearest Street View panorama in Google Maps; needs no API key.
     */
    public function streetViewUrl(): string
    {
        return 'https://www.google.com/maps/@?api=1&map_action=pano&viewpoint='.$this->latitude.','.$this->longitude;
    }

    /**
     * The interactive 360° panorama for an iframe, via the keyed (and free) Maps Embed API.
     */
    public function streetViewEmbedUrl(string $apiKey): string
    {
        return 'https://www.google.com/maps/embed/v1/streetview?'.http_build_query([
            'key' => $apiKey,
            'location' => $this->latitude.','.$this->longitude,
            'fov' => 90,
            'pitch' => 10,
        ]);
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
     * @return array{name: string, url: string, place: string, photo: ?string, lat: float, lng: float}
     */
    public function toMarker(): array
    {
        return [
            'name' => $this->name,
            'url' => route('statues.show', $this),
            'place' => $this->place(),
            'photo' => $this->thumbnailUrl(),
            'lat' => $this->latitude,
            'lng' => $this->longitude,
        ];
    }
}
