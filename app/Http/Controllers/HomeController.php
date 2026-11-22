<?php

namespace App\Http\Controllers;

use App\Models\Regency;
use App\Models\Statue;
use App\Support\StatuePhotos;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    // Statues with the team's own field photos, shown in the photo mosaic under the hero when available.
    private const HERO_NUMBERS = [4, 1, 2];

    // Hand-picked cover photos without Street View interface overlays; other regencies use their first photo.
    private const REGENCY_COVERS = [
        'bangli' => '044-1.jpg',
        'gianyar' => '064-1.jpg',
        'karangasem' => '056-1.jpg',
        'klungkung' => '076-3.jpg',
        'tabanan' => '099-1.jpg',
    ];

    private const FEATURED_COUNT = 6;

    public function __invoke(): View
    {
        $statues = Statue::query()->with(['regency', 'themes', 'junctionType'])->orderBy('source_no')->get();
        $withPhotos = $statues->filter(fn (Statue $statue) => $statue->photoUrl() !== null)->values();

        return view('pages.home', [
            'statueCount' => $statues->count(),
            'coveredRegencyCount' => $statues->pluck('regency_id')->unique()->count(),
            'regencyCount' => Regency::query()->count(),
            'photoCount' => $statues->sum(fn (Statue $statue) => count($statue->photos ?? [])),
            'hero' => $this->hero($withPhotos),
            'regencyTiles' => $this->regencyTiles($statues, $withPhotos),
            'featured' => $this->featured($withPhotos),
            'markers' => $statues->map->toMarker()->values(),
        ]);
    }

    /**
     * @param  Collection<int, Statue>  $withPhotos
     * @return Collection<int, Statue>
     */
    private function hero(Collection $withPhotos): Collection
    {
        $chosen = collect(self::HERO_NUMBERS)
            ->map(fn (int $number) => $withPhotos->firstWhere('source_no', $number))
            ->filter();

        return $chosen
            ->concat($withPhotos->reject(fn (Statue $statue) => in_array($statue->source_no, self::HERO_NUMBERS, true)))
            ->take(count(self::HERO_NUMBERS))
            ->values();
    }

    /**
     * @param  Collection<int, Statue>  $statues
     * @param  Collection<int, Statue>  $withPhotos
     * @return Collection<int, array{regency: Regency, count: int, cover: ?string}>
     */
    private function regencyTiles(Collection $statues, Collection $withPhotos): Collection
    {
        return Regency::query()->orderBy('name')->get()
            ->map(fn (Regency $regency) => [
                'regency' => $regency,
                'count' => $statues->where('regency_id', $regency->id)->count(),
                'cover' => $this->regencyCover($regency, $withPhotos),
            ])
            ->filter(fn (array $tile) => $tile['count'] > 0)
            ->values();
    }

    /**
     * @param  Collection<int, Statue>  $withPhotos
     */
    private function regencyCover(Regency $regency, Collection $withPhotos): ?string
    {
        $chosen = self::REGENCY_COVERS[$regency->slug] ?? null;

        if ($chosen !== null && is_file(StatuePhotos::directory().'/'.$chosen)) {
            return StatuePhotos::thumbnailUrl($chosen);
        }

        return $withPhotos->firstWhere('regency_id', $regency->id)?->thumbnailUrl();
    }

    /**
     * A different selection each day, the same for everyone within the day.
     *
     * @param  Collection<int, Statue>  $withPhotos
     * @return Collection<int, Statue>
     */
    private function featured(Collection $withPhotos): Collection
    {
        $day = now()->toDateString();

        return $withPhotos
            ->sortBy(fn (Statue $statue) => crc32($day.$statue->slug))
            ->take(self::FEATURED_COUNT)
            ->values();
    }
}
