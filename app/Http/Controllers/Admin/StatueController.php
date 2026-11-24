<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatueStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StatueRequest;
use App\Models\JunctionType;
use App\Models\Regency;
use App\Models\Statue;
use App\Models\Theme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StatueController extends Controller
{
    private const SAVE_ATTEMPTS = 3;

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = StatueStatus::tryFrom((string) $request->query('status', ''));

        $statues = Statue::withUnpublished()
            ->with(['regency', 'themes'])
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.Str::lower($search).'%';
                $query->where(fn ($q) => $q
                    ->whereRaw('lower(name) like ?', [$like])
                    ->orWhereRaw('lower(district) like ?', [$like])
                    ->orWhereRaw('lower(coalesce(village, \'\')) like ?', [$like])
                    ->when(ctype_digit($search), fn ($q) => $q->orWhere('source_no', (int) $search)));
            })
            ->when($status, fn ($query) => $query->where('status', $status->value))
            ->orderBy('source_no')
            ->get();

        return view('admin.index', [
            'statues' => $statues,
            'search' => $search,
            'status' => $status,
            'counts' => Statue::withUnpublished()->toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function create(): View
    {
        return view('admin.form', $this->formData(new Statue(['status' => StatueStatus::Published])));
    }

    public function store(StatueRequest $request): RedirectResponse
    {
        $attributes = $request->statueAttributes();

        // Postgres refuses FOR UPDATE on max(), so the unique indexes on source_no and slug guard
        // against two saves at once and the next number is simply retried.
        $statue = retry(self::SAVE_ATTEMPTS, function () use ($attributes) {
            $statue = new Statue($attributes);
            $statue->source_no = (int) Statue::withUnpublished()->max('source_no') + 1;
            $statue->slug = $this->uniqueSlug($attributes['name']);
            $statue->photos = [];
            $statue->save();

            return $statue;
        }, when: fn (\Throwable $e) => $e instanceof UniqueConstraintViolationException);

        return redirect()->route('admin.statues.edit', $statue->id)
            ->with('status', 'Patung tersimpan dan sudah tampil di website. Tambahkan foto di bawah.');
    }

    public function edit(Statue $adminStatue): View
    {
        return view('admin.form', $this->formData($adminStatue->load('themes')));
    }

    public function update(StatueRequest $request, Statue $adminStatue): RedirectResponse
    {
        // The slug stays as it was: it is the statue's public address and may already be shared or printed.
        $adminStatue->fill($request->statueAttributes())->save();

        return redirect()->route('admin.statues.edit', $adminStatue->id)->with('status', 'Perubahan tersimpan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Statue $statue): array
    {
        return [
            'statue' => $statue,
            'regencies' => Regency::query()->orderBy('name')->get(),
            'junctionTypes' => JunctionType::query()->orderBy('sort_order')->get(),
            'themes' => Theme::query()->orderBy('sort_order')->get(),
            'statuses' => StatueStatus::cases(),
        ];
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;

        for ($suffix = 2; Statue::withUnpublished()->where('slug', $slug)->exists(); $suffix++) {
            $slug = $base.'-'.$suffix;
        }

        return $slug;
    }
}
