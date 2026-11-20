<?php

namespace App\Http\Controllers;

use App\Models\Statue;
use Illuminate\View\View;

class MapController extends Controller
{
    public function __invoke(): View
    {
        $statues = Statue::query()->with('regency')->orderBy('name')->get();

        return view('pages.map', [
            'statueCount' => $statues->count(),
            'markers' => $statues->map->toMarker()->values(),
        ]);
    }
}
