@props(['statue', 'review'])

@php
    $record = $review['record'];
    $chosen = $record?->themes ?? [];
    $proposed = $statue->proposed_themes ?? [];
    $themes = $review['themes']->keyBy('slug');
    $progress = $review['progress'];
    $percent = $progress['total'] > 0 ? round($progress['done'] / $progress['total'] * 100) : 0;
    $layout = [['itihasa', 'tokoh-babad'], 'monumen', ['ikon-daerah', 'figur-kanda'], ['religi']];
    $monumen = $review['themes']->where('group', 'monumen')->values();
    $choice = 'w-full rounded-xl border border-stone-300 bg-white px-2 py-2.5 text-sm font-semibold text-ink transition hover:border-stone-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 aria-pressed:border-ink aria-pressed:bg-ink aria-pressed:text-paper';
    $chip = 'rounded-full border border-stone-300 px-3 py-1 text-xs font-medium aria-pressed:border-ink aria-pressed:bg-ink aria-pressed:text-paper';
@endphp

<section
    data-review-panel
    data-url="{{ route('reviews.update', $statue) }}"
    data-saved-label="{{ __('Tersimpan pukul :time', ['time' => '__TIME__']) }}"
    data-retry-label="{{ __('Gagal menyimpan. Mencoba lagi otomatis, jangan tutup halaman ini dulu.') }}"
    data-error-label="{{ __('Gagal menyimpan. Muat ulang halaman, lalu ulangi pilihan.') }}"
    data-progress-label="{{ __(':done dari :total diklasifikasi', ['done' => '__DONE__', 'total' => '__TOTAL__']) }}"
    data-done-label="{{ __('Sudah') }}"
    data-todo-label="{{ __('Belum') }}"
    aria-labelledby="review-heading"
    class="rounded-2xl border-2 border-brand-200 bg-white p-5 shadow-lg shadow-stone-900/5 lg:max-h-[calc(100vh-9rem)] lg:overflow-y-auto"
>
    <div class="flex items-baseline justify-between gap-3">
        <h2 id="review-heading" class="font-display text-lg font-semibold">{{ __('Klasifikasi tema') }}</h2>
        <a href="{{ route('reviews.index') }}" class="text-xs text-stone-600 underline decoration-stone-300 underline-offset-4 hover:text-ink" data-review-progress>{{ __(':done dari :total diklasifikasi', $progress) }}</a>
    </div>
    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-stone-100" aria-hidden="true">
        <div class="h-full rounded-full bg-brand-600 transition-all" style="width: {{ $percent }}%" data-review-bar></div>
    </div>

    <div class="mt-4 rounded-xl bg-stone-50 px-3 py-2 text-sm text-stone-700">
        <p>{{ __('Usulan tim') }}: <strong class="text-ink">{{ $statue->proposalLabel($review['themes']) ?? __('belum ada') }}</strong></p>
        @if ($statue->proposal_note)
            <p class="mt-1 text-xs text-stone-500">{{ $statue->proposal_note }}</p>
        @endif
    </div>

    <fieldset class="mt-4">
        <legend class="text-xs font-semibold tracking-wide text-stone-600 uppercase">{{ __('Tema patung, boleh lebih dari satu') }}</legend>
        <div class="mt-2 space-y-2">
            @foreach ($layout as $row)
                @if ($row === 'monumen')
                    <div class="rounded-xl border border-dashed border-stone-300 p-2">
                        <p class="px-1 pb-1.5 text-xs font-semibold text-stone-600">{{ __('Monumen') }}</p>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach ($monumen as $theme)
                                <button type="button" data-review-theme="{{ $theme->slug }}" aria-pressed="{{ in_array($theme->slug, $chosen, true) ? 'true' : 'false' }}" aria-label="{{ $theme->name }}" class="{{ $choice }}">
                                    {{ Str::after($theme->name, '· ') }}
                                    @if (in_array($theme->slug, $proposed, true))
                                        <span class="block text-[11px] font-normal opacity-70">{{ __('usulan tim') }}</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div @class(['grid gap-2', 'grid-cols-2' => count($row) > 1])>
                        @foreach ($row as $slug)
                            @if ($theme = $themes->get($slug))
                                <button type="button" data-review-theme="{{ $theme->slug }}" aria-pressed="{{ in_array($theme->slug, $chosen, true) ? 'true' : 'false' }}" class="{{ $choice }}">
                                    {{ $theme->name }}
                                    @if (in_array($theme->slug, $proposed, true))
                                        <span class="block text-[11px] font-normal opacity-70">{{ __('usulan tim') }}</span>
                                    @endif
                                </button>
                            @endif
                        @endforeach
                    </div>
                @endif
            @endforeach
        </div>
        <p class="mt-2 text-xs text-stone-500">{{ __('Klik sekali lagi untuk membatalkan pilihan.') }}</p>
    </fieldset>

    <details class="mt-3 rounded-xl bg-stone-50 text-sm">
        <summary class="cursor-pointer px-3 py-2 font-medium text-stone-700">{{ __('Arti tiap tema') }}</summary>
        <dl class="space-y-2 px-3 pb-3">
            @foreach ($review['themes'] as $theme)
                <div>
                    <dt class="font-semibold text-ink">{{ $theme->name }}</dt>
                    <dd class="text-stone-600">{{ $theme->description }}</dd>
                </div>
            @endforeach
        </dl>
    </details>

    <label class="mt-4 block">
        <span class="text-xs font-semibold tracking-wide text-stone-600 uppercase">{{ __('Catatan') }}</span>
        <textarea
            data-review-notes
            rows="3"
            maxlength="5000"
            placeholder="{{ __('Alasan, koreksi data, atau hal yang perlu dicek tim') }}"
            class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm placeholder:text-stone-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 focus:outline-none"
        >{{ $record?->notes }}</textarea>
    </label>

    <p class="mt-1 min-h-5 text-xs text-stone-500" data-review-status role="status" aria-live="polite">
        {{ $record ? __('Tersimpan pukul :time', ['time' => $record->updated_at->timezone('Asia/Makassar')->format('H.i')]) : __('Pilihan dan catatan tersimpan otomatis.') }}
    </p>

    <div class="mt-4 space-y-3">
        @if ($review['nextUnreviewed'])
            <x-button-link :href="route('statues.show', $review['nextUnreviewed'])" class="w-full">{{ __('Lanjut ke yang belum diklasifikasi') }} →</x-button-link>
        @elseif ($progress['done'] === $progress['total'])
            <p class="rounded-xl bg-green-50 px-3 py-2 text-center text-sm font-medium text-green-800">{{ __('Semua patung sudah diklasifikasi.') }}</p>
        @else
            <p class="rounded-xl bg-stone-50 px-3 py-2 text-center text-sm text-stone-700">{{ __('Tinggal patung ini yang belum diklasifikasi.') }}</p>
        @endif

        <nav aria-label="{{ __('Urutan patung') }}" class="grid grid-cols-2 gap-2">
            @foreach ([['statue' => $review['previous'], 'label' => '← '.__('Sebelumnya')], ['statue' => $review['next'], 'label' => __('Berikutnya').' →']] as $step)
                @if ($step['statue'])
                    <a href="{{ route('statues.show', $step['statue']) }}" class="inline-flex h-10 items-center justify-center rounded-full border border-stone-300 bg-white px-3 text-sm font-medium text-ink transition hover:border-stone-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">{{ $step['label'] }}</a>
                @else
                    <span class="inline-flex h-10 items-center justify-center rounded-full border border-stone-200 px-3 text-sm text-stone-400" aria-disabled="true">{{ $step['label'] }}</span>
                @endif
            @endforeach
        </nav>
        <a href="{{ route('reviews.index') }}" class="block text-center text-sm font-medium text-brand-700 underline decoration-brand-200 underline-offset-4 hover:text-brand-800">{{ __('Lihat daftar semua patung') }}</a>
    </div>

    <details class="mt-4 rounded-xl border border-stone-200" data-review-search>
        <summary class="cursor-pointer px-3 py-2 text-sm font-medium">{{ __('Cari patung lain') }}</summary>
        <div class="space-y-2 border-t border-stone-200 p-3">
            <input
                type="search"
                data-review-search-input
                placeholder="{{ __('Ketik nama atau lokasi') }}"
                aria-label="{{ __('Cari patung') }}"
                class="h-10 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 focus:outline-none"
            >
            <div class="flex gap-1.5" role="group" aria-label="{{ __('Saring menurut status') }}">
                <button type="button" data-review-filter="all" aria-pressed="true" class="{{ $chip }}">{{ __('Semua') }}</button>
                <button type="button" data-review-filter="todo" aria-pressed="false" class="{{ $chip }}">{{ __('Belum') }}</button>
                <button type="button" data-review-filter="done" aria-pressed="false" class="{{ $chip }}">{{ __('Sudah') }}</button>
            </div>
            <ul class="max-h-64 divide-y divide-stone-100 overflow-y-auto text-sm" data-review-search-list></ul>
        </div>
    </details>

    <script type="application/json" data-review-items>@json($review['items'])</script>
</section>
