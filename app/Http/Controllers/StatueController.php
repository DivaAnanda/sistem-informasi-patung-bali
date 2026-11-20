<?php

namespace App\Http\Controllers;

use App\Models\JunctionType;
use App\Models\Regency;
use App\Models\Statue;
use App\Models\StatueType;
use App\Services\ReviewBoard;
use App\Support\Expert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StatueController extends Controller
{
    private const PER_PAGE = 12;

    private const NEARBY_COUNT = 3;

    private const FILTER_KEYS = ['q', 'kabupaten', 'jenis', 'persimpangan'];

    private const MAX_FILTER_LENGTH = 100;

    public function index(Request $request): View|RedirectResponse
    {
        $filters = $this->filters($request);

        $statues = Statue::query()
            ->with(['regency', 'statueType', 'junctionType'])
            ->filter($filters)
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        if ($statues->currentPage() > $statues->lastPage()) {
            return redirect($statues->url($statues->lastPage()));
        }

        $statueTypes = StatueType::query()->withCount('statues')->orderBy('sort_order')->get();

        return view('statues.index', [
            'statues' => $statues,
            'filters' => $filters,
            'totalCount' => Statue::query()->count(),
            'regencies' => Regency::query()->withCount('statues')->orderBy('name')->get(),
            'statueTypes' => $statueTypes,
            'isClassified' => $statueTypes->sum('statues_count') > 0,
            'junctionTypes' => JunctionType::query()->withCount('statues')->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Filters come from hand-editable URLs, so malformed values are dropped or trimmed instead of rejected.
     *
     * @return array{q?: string, kabupaten?: string, jenis?: string, persimpangan?: string}
     */
    private function filters(Request $request): array
    {
        return collect($request->only(self::FILTER_KEYS))
            ->filter(fn (mixed $value) => is_string($value) && trim($value) !== '')
            ->map(fn (string $value) => Str::limit(trim($value), self::MAX_FILTER_LENGTH, ''))
            ->all();
    }

    public function show(Statue $statue): View
    {
        $statue->load(['regency', 'statueType', 'junctionType', 'proposedType']);

        // The dataset is small (~100 rows), so distances are computed in PHP to stay database-agnostic.
        $nearby = Statue::query()
            ->with(['regency', 'statueType', 'junctionType'])
            ->whereKeyNot($statue->getKey())
            ->get()
            ->map(fn (Statue $other) => ['statue' => $other, 'km' => $statue->distanceTo($other)])
            ->sortBy('km')
            ->take(self::NEARBY_COUNT)
            ->values();

        return view('statues.show', [
            'statue' => $statue,
            'nearby' => $nearby,
            'marker' => $statue->toMarker(),
            'review' => Expert::active() ? $this->reviewContext($statue) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function reviewContext(Statue $statue): array
    {
        $board = new ReviewBoard;

        return [
            'record' => $board->reviewFor($statue),
            'progress' => $board->progress(),
            'previous' => $board->previous($statue),
            'next' => $board->next($statue),
            'nextUnreviewed' => $board->nextUnreviewed($statue),
            'items' => $board->searchItems($statue),
        ];
    }
}
