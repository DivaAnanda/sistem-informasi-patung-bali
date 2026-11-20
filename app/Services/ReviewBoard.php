<?php

namespace App\Services;

use App\Models\Statue;
use App\Models\StatueReview;
use Illuminate\Support\Collection;

/**
 * Every statue in sheet order together with the expert's review, for progress and navigation.
 */
final class ReviewBoard
{
    /** @var Collection<int, Statue> */
    private Collection $statues;

    /** @var Collection<int, StatueReview> keyed by source_no */
    private Collection $reviews;

    public function __construct()
    {
        $this->statues = Statue::query()->with(['regency', 'proposedType'])->orderBy('source_no')->get()->values();
        $this->reviews = StatueReview::query()->get()->keyBy('source_no');
    }

    /**
     * @return Collection<int, Statue>
     */
    public function statues(): Collection
    {
        return $this->statues;
    }

    public function reviewFor(Statue $statue): ?StatueReview
    {
        return $this->reviews->get($statue->source_no);
    }

    public function isReviewed(Statue $statue): bool
    {
        return (bool) $this->reviewFor($statue)?->isReviewed();
    }

    /**
     * @return array{done: int, total: int}
     */
    public function progress(): array
    {
        return [
            'done' => $this->statues->filter(fn (Statue $statue) => $this->isReviewed($statue))->count(),
            'total' => $this->statues->count(),
        ];
    }

    public function previous(Statue $statue): ?Statue
    {
        return $this->statues->get($this->position($statue) - 1);
    }

    public function next(Statue $statue): ?Statue
    {
        return $this->statues->get($this->position($statue) + 1);
    }

    /**
     * The first unreviewed statue after this one, wrapping around to the start.
     */
    public function nextUnreviewed(Statue $statue): ?Statue
    {
        $position = $this->position($statue);
        $ordered = $this->statues->slice($position + 1)->concat($this->statues->take($position));

        return $ordered->first(fn (Statue $other) => ! $this->isReviewed($other));
    }

    /**
     * Compact rows for the in-page search list.
     *
     * @return list<array{name: string, place: string, url: string, reviewed: bool, current: bool}>
     */
    public function searchItems(?Statue $current = null): array
    {
        return $this->statues->map(fn (Statue $statue) => [
            'name' => $statue->name,
            'place' => $statue->place(),
            'url' => route('statues.show', $statue),
            'reviewed' => $this->isReviewed($statue),
            'current' => $current?->is($statue) ?? false,
        ])->values()->all();
    }

    private function position(Statue $statue): int
    {
        return (int) $this->statues->search(fn (Statue $other) => $other->is($statue));
    }
}
