@php
    $percent = $progress['total'] > 0 ? round($progress['done'] / $progress['total'] * 100) : 0;
    $statusOptions = collect([
        ['value' => 'belum', 'label' => __('Belum diklasifikasi'), 'count' => $progress['total'] - $progress['done']],
        ['value' => 'sudah', 'label' => __('Sudah diklasifikasi'), 'count' => $progress['done']],
    ]);
@endphp

<x-layouts.app :title="__('Klasifikasi tema patung')">
    <div class="mx-auto max-w-6xl px-4 pt-10 sm:px-6">
        <header class="grid gap-8 lg:grid-cols-[1fr_22rem] lg:items-end">
            <div class="max-w-2xl">
                <h1 class="font-display text-3xl font-semibold sm:text-4xl">{{ __('Klasifikasi tema patung') }}</h1>
                <ol class="mt-4 list-decimal space-y-1 pl-5 text-stone-700 marker:text-stone-400">
                    <li>{{ __('Buka patung, lihat foto dan deskripsinya.') }}</li>
                    <li>{{ __('Pilih tema patung. Boleh lebih dari satu, misalnya Tokoh Babad sekaligus Monumen · Tokoh Sejarah.') }}</li>
                    <li>{{ __('Tulis catatan bila perlu. Semua tersimpan otomatis, lalu lanjut ke patung berikutnya.') }}</li>
                </ol>
            </div>

            <div class="rounded-2xl border border-stone-200 bg-white p-5">
                <p class="text-sm text-stone-600">{{ __('Kemajuan') }}</p>
                <p class="mt-1 font-display text-3xl font-semibold">{{ $progress['done'] }}<span class="text-lg text-stone-500"> / {{ $progress['total'] }}</span></p>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-stone-100" aria-hidden="true">
                    <div class="h-full rounded-full bg-brand-600" style="width: {{ $percent }}%"></div>
                </div>
                @if ($firstUnreviewed)
                    <x-button-link :href="\App\Support\Expert::statueUrl($firstUnreviewed)" class="mt-4 w-full">{{ __('Mulai dari yang belum diklasifikasi') }} →</x-button-link>
                @else
                    <p class="mt-4 rounded-xl bg-green-50 px-3 py-2 text-center text-sm font-medium text-green-800">{{ __('Semua patung sudah diklasifikasi.') }}</p>
                @endif
            </div>
        </header>

        <form method="GET" action="{{ route('reviews.index') }}" role="search" class="mt-10 flex flex-col gap-4 rounded-2xl border border-stone-200 bg-white p-4 xl:flex-row xl:items-end">
            <label class="flex min-w-0 flex-[2] flex-col gap-1.5">
                <span class="text-xs font-semibold tracking-wide text-stone-600 uppercase">{{ __('Cari') }}</span>
                <input type="search" name="q" value="{{ $filters['q'] }}" maxlength="100" placeholder="{{ __('Nama patung, kecamatan, atau desa') }}"
                    class="h-11 w-full rounded-xl border border-stone-300 px-3 text-sm placeholder:text-stone-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 focus:outline-none">
            </label>
            <x-filter-select
                name="kabupaten"
                :label="__('Kabupaten/kota')"
                :placeholder="__('Semua kabupaten')"
                :selected="$filters['kabupaten'] ?: null"
                :options="$regencies->map(fn ($regency) => ['value' => $regency->slug, 'label' => $regency->name, 'count' => $board->statues()->where('regency_id', $regency->id)->count()])"
            />
            <x-filter-select name="status" :label="__('Status')" :placeholder="__('Semua status')" :selected="$filters['status']" :options="$statusOptions" />
            <div class="flex gap-2">
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-ink px-5 text-sm font-semibold text-paper transition hover:bg-brand-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">{{ __('Terapkan') }}</button>
                @if (array_filter($filters))
                    <x-button-link :href="route('reviews.index')" variant="secondary">{{ __('Reset') }}</x-button-link>
                @endif
            </div>
        </form>

        <p class="mt-8 text-sm text-stone-600" aria-live="polite">{{ __(':count dari :total patung', ['count' => $statues->count(), 'total' => $progress['total']]) }}</p>

        <h2 class="sr-only">{{ __('Hasil pencarian') }}</h2>
        <ul class="mt-3 divide-y divide-stone-200 overflow-hidden rounded-2xl border border-stone-200 bg-white">
            @forelse ($statues as $statue)
                @php($record = $board->reviewFor($statue))
                <li>
                    <a href="{{ \App\Support\Expert::statueUrl($statue) }}" class="flex flex-col gap-2 px-5 py-4 transition hover:bg-stone-50 sm:flex-row sm:items-center sm:gap-6">
                        <span class="w-10 shrink-0 font-mono text-xs text-stone-500">#{{ $statue->source_no }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block font-semibold text-ink">{{ $statue->name }}</span>
                            <span class="block text-sm text-stone-600">{{ $statue->place() }}</span>
                        </span>
                        <span class="text-sm text-stone-600 sm:w-44">{{ __('Usulan') }}: {{ $statue->proposalLabel($board->themes()) ?? '–' }}</span>
                        <span class="sm:w-44">
                            @if ($record?->isReviewed())
                                <x-badge tone="brand">✓ {{ $board->themeLabel($record->themes) }}</x-badge>
                            @else
                                <x-badge>{{ __('Belum diklasifikasi') }}</x-badge>
                            @endif
                        </span>
                    </a>
                </li>
            @empty
                <li class="px-5 py-12 text-center text-stone-600">{{ __('Tidak ada patung yang cocok.') }}</li>
            @endforelse
        </ul>
    </div>
</x-layouts.app>
