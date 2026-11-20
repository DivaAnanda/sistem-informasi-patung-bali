<?php

namespace App\Http\Controllers;

use App\Models\Regency;
use App\Models\Statue;
use Illuminate\View\View;

class HomeController extends Controller
{
    private const FEATURED_COUNT = 6;

    public function __invoke(): View
    {
        $statues = Statue::query()->with(['regency', 'statueType', 'junctionType'])->orderBy('source_no')->get();

        return view('pages.home', [
            'statueCount' => $statues->count(),
            'coveredRegencyCount' => $statues->pluck('regency_id')->unique()->count(),
            'regencyCount' => Regency::query()->count(),
            'featured' => $statues->take(self::FEATURED_COUNT),
            'markers' => $statues->map->toMarker()->values(),
        ]);
    }
}
