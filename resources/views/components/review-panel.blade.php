@props(['statue', 'review'])

@php
    $record = $review['record'];
    $selected = $record?->statue_type;
    $isIconic = (bool) $record?->is_iconic;
    $progress = $review['progress'];
    $percent = $progress['total'] > 0 ? round($progress['done'] / $progress['total'] * 100) : 0;
    $types = ['religius' => __('Religius'), 'budaya' => __('Budaya'), 'dekoratif' => __('Dekoratif')];
    $proposedSlug = $statue->proposedType?->slug;
    $choice = 'rounded-xl border border-stone-300 bg-white px-2 py-2.5 text-sm font-semibold text-ink transition hover:border-stone-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 aria-pressed:border-ink aria-pressed:bg-ink aria-pressed:text-paper';
    $chip = 'rounded-full border border-stone-300 px-3 py-1 text-xs font-medium aria-pressed:border-ink aria-pressed:bg-ink aria-pressed:text-paper';
@endphp

<section
    data-review-panel
    data-url="{{ route('reviews.update', $statue) }}"
    data-saved-label="{{ __('Tersimpan pukul :time', ['time' => '__TIME__']) }}"
    data-error-label="{{ __('Gagal menyimpan. Periksa koneksi, lalu coba lagi.') }}"
    data-progress-label="{{ __(':done dari :total diperiksa', ['done' => '__DONE__', 'total' => '__TOTAL__']) }}"
    data-done-label="{{ __('Sudah') }}"
    data-todo-label="{{ __('Belum') }}"
    aria-labelledby="review-heading"
    class="rounded-2xl border-2 border-brand-200 bg-white p-5 shadow-lg shadow-stone-900/5 lg:max-h-[calc(100vh-9rem)] lg:overflow-y-auto"
>
    <div class="flex items-baseline justify-between gap-3">
        <h2 id="review-heading" class="font-display text-lg font-semibold">{{ __('Validasi pakar') }}</h2>
        <span class="text-xs text-stone-600" data-review-progress>{{ __(':done dari :total diperiksa', $progress) }}</span>
    </div>
    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-stone-100" aria-hidden="true">
        <div class="h-full rounded-full bg-brand-600 transition-all" style="width: {{ $percent }}%" data-review-bar></div>
    </div>

    <p class="mt-4 rounded-xl bg-stone-50 px-3 py-2 text-sm text-stone-700">
        {{ __('Usulan tim') }}: <strong class="text-ink">{{ $statue->proposalLabel() ?? __('belum ada') }}</strong>
    </p>

    <fieldset class="mt-4">
        <legend class="text-xs font-semibold tracking-wide text-stone-600 uppercase">{{ __('Jenis patung, pilih satu') }}</legend>
        <div class="mt-2 grid grid-cols-3 gap-2">
            @foreach ($types as $value => $label)
                <button type="button" data-review-type="{{ $value }}" aria-pressed="{{ $selected === $value ? 'true' : 'false' }}" class="{{ $choice }}">
                    {{ $label }}
                    @if ($proposedSlug === $value)
                        <span class="block text-[11px] font-normal opacity-70">{{ __('usulan tim') }}</span>
                    @endif
                </button>
            @endforeach
        </div>
    </fieldset>

    <div class="mt-3">
        <button type="button" data-review-iconic aria-pressed="{{ $isIconic ? 'true' : 'false' }}" class="{{ $choice }} w-full">
            {{ __('Ikonik (penanda wilayah)') }}
            @if ($statue->proposed_iconic)
                <span class="block text-[11px] font-normal opacity-70">{{ __('usulan tim') }}</span>
            @endif
        </button>
        <p class="mt-1 text-xs text-stone-500">{{ __('Tambahan. Boleh dipilih bersama salah satu jenis di atas. Klik sekali lagi untuk membatalkan pilihan.') }}</p>
    </div>

    <label class="mt-4 block">
        <span class="text-xs font-semibold tracking-wide text-stone-600 uppercase">{{ __('Catatan') }}</span>
        <textarea
            data-review-notes
            rows="4"
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
            <x-button-link :href="route('statues.show', $review['nextUnreviewed'])" class="w-full">{{ __('Lanjut ke yang belum diperiksa') }} →</x-button-link>
        @elseif ($progress['done'] === $progress['total'])
            <p class="rounded-xl bg-green-50 px-3 py-2 text-center text-sm font-medium text-green-800">{{ __('Semua patung sudah diperiksa.') }}</p>
        @else
            <p class="rounded-xl bg-stone-50 px-3 py-2 text-center text-sm text-stone-700">{{ __('Tinggal patung ini yang belum diperiksa.') }}</p>
        @endif

        <div class="flex items-center justify-between gap-2 text-sm">
            @if ($review['previous'])
                <x-text-link :href="route('statues.show', $review['previous'])">← {{ __('Sebelumnya') }}</x-text-link>
            @else
                <span></span>
            @endif
            <x-text-link :href="route('reviews.index')">{{ __('Daftar') }}</x-text-link>
            @if ($review['next'])
                <x-text-link :href="route('statues.show', $review['next'])">{{ __('Berikutnya') }} →</x-text-link>
            @else
                <span></span>
            @endif
        </div>
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
