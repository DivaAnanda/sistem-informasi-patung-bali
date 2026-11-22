<?php

namespace App\Http\Controllers;

use App\Models\Regency;
use App\Models\Statue;
use App\Models\StatueReview;
use App\Models\Theme;
use App\Services\ReviewBoard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReviewController extends Controller
{
    private const STATUSES = ['sudah', 'belum'];

    public function index(Request $request): View
    {
        $board = new ReviewBoard;
        $q = Str::lower(trim((string) $request->string('q')));
        $regency = (string) $request->string('kabupaten');
        $status = in_array($request->query('status'), self::STATUSES, true) ? $request->query('status') : null;

        $statues = $board->statues()
            ->when($q !== '', fn ($items) => $items->filter(
                fn (Statue $statue) => Str::contains(Str::lower($statue->name.' '.$statue->place()), $q)
            ))
            ->when($regency !== '', fn ($items) => $items->filter(fn (Statue $statue) => $statue->regency->slug === $regency))
            ->when($status !== null, fn ($items) => $items->filter(
                fn (Statue $statue) => $board->isReviewed($statue) === ($status === 'sudah')
            ));

        return view('reviews.index', [
            'board' => $board,
            'statues' => $statues,
            'filters' => ['q' => $q, 'kabupaten' => $regency, 'status' => $status],
            'regencies' => Regency::query()->orderBy('name')->get(),
            'progress' => $board->progress(),
            'firstUnreviewed' => $board->statues()->first(fn (Statue $statue) => ! $board->isReviewed($statue)),
        ]);
    }

    /**
     * Only the fields sent are changed: the theme buttons send the themes, the notes box sends the
     * notes. A second open tab therefore cannot wipe text it never saw.
     */
    public function update(Request $request, Statue $statue): JsonResponse
    {
        $catalog = Theme::query()->orderBy('sort_order')->pluck('slug');

        $data = $request->validate([
            'themes' => ['sometimes', 'array'],
            'themes.*' => ['string', Rule::in($catalog->all())],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ]);

        abort_if($data === [], 422, 'Tidak ada perubahan yang dikirim.');

        if (array_key_exists('themes', $data)) {
            $data['themes'] = $catalog->intersect($data['themes'])->values()->all();
        }

        if (array_key_exists('notes', $data)) {
            $data['notes'] = filled($data['notes']) ? trim($data['notes']) : null;
        }

        $review = StatueReview::query()->firstOrNew(['source_no' => $statue->source_no]);
        $review->fill($data);

        // The name records which statue the themes were chosen for, so a notes-only save must not
        // overwrite it and hide the export's "number reused" warning.
        if (! $review->exists || array_key_exists('themes', $data)) {
            $review->statue_name = $statue->name;
        }
        $review->reviewed_at = $review->isReviewed() ? ($review->reviewed_at ?? now()) : null;
        $review->save();

        return response()->json([
            'reviewed' => $review->isReviewed(),
            'saved_at' => $review->updated_at->timezone('Asia/Makassar')->format('H.i'),
            'progress' => (new ReviewBoard)->progress(),
        ]);
    }
}
