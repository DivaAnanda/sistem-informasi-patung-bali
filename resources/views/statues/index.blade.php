<x-layouts.app :title="__('Jelajahi patung')">
    <div class="mx-auto max-w-6xl px-4 pt-10 sm:px-6">
        <header class="max-w-2xl">
            <h1 class="font-display text-3xl font-semibold sm:text-4xl">{{ __('Jelajahi patung') }}</h1>
            <p class="mt-3 text-stone-700">{{ __('Cari berdasarkan nama atau lokasi, lalu saring menurut kabupaten dan bentuk persimpangan.') }}</p>
        </header>

        <form method="GET" action="{{ route('statues.index') }}" role="search" class="mt-8 flex flex-col gap-4 rounded-2xl border border-stone-200 bg-white p-4 lg:flex-row lg:items-end">
            <label class="flex min-w-0 flex-[2] flex-col gap-1.5">
                <span class="text-xs font-semibold tracking-wide text-stone-600 uppercase">{{ __('Cari') }}</span>
                <input
                    type="search"
                    name="q"
                    value="{{ $filters['q'] ?? '' }}"
                    maxlength="100"
                    placeholder="{{ __('Nama patung, kecamatan, atau desa') }}"
                    class="h-11 w-full rounded-xl border border-stone-300 px-3 text-sm placeholder:text-stone-400 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 focus:outline-none"
                >
            </label>

            <x-filter-select
                name="kabupaten"
                :label="__('Kabupaten/kota')"
                :placeholder="__('Semua kabupaten')"
                :selected="$filters['kabupaten'] ?? null"
                :options="$regencies->map(fn ($regency) => ['value' => $regency->slug, 'label' => $regency->name, 'count' => $regency->statues_count])"
            />

            @if ($isClassified)
                <x-filter-select
                    name="jenis"
                    :label="__('Jenis patung')"
                    :placeholder="__('Semua jenis')"
                    :selected="$filters['jenis'] ?? null"
                    :options="$statueTypes->map(fn ($type) => ['value' => $type->slug, 'label' => $type->name, 'count' => $type->statues_count])"
                />
            @endif

            <x-filter-select
                name="persimpangan"
                :label="__('Jenis persimpangan')"
                :placeholder="__('Semua jenis')"
                :selected="$filters['persimpangan'] ?? null"
                :options="$junctionTypes->map(fn ($type) => ['value' => $type->slug, 'label' => $type->name, 'count' => $type->statues_count])"
            />

            <div class="flex gap-2">
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-ink px-5 text-sm font-semibold text-paper transition hover:bg-brand-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">
                    {{ __('Terapkan') }}
                </button>
                @if (array_filter($filters))
                    <x-button-link :href="route('statues.index')" variant="secondary">{{ __('Reset') }}</x-button-link>
                @endif
            </div>
        </form>

        @unless ($isClassified)
            <p class="mt-3 text-sm text-stone-600">
                {{ __('Klasifikasi jenis patung (religius, budaya, dekoratif, ikonik) sedang divalidasi bersama pakar dan akan ditampilkan setelah final.') }}
            </p>
        @endunless

        <p class="mt-10 text-sm text-stone-600" aria-live="polite">
            {{ __(':count dari :total patung', ['count' => $statues->total(), 'total' => $totalCount]) }}
        </p>

        @if ($statues->isEmpty())
            <div class="mt-4 rounded-2xl border border-dashed border-stone-300 px-6 py-16 text-center">
                <p class="font-display text-xl font-semibold">{{ __('Tidak ada patung yang cocok.') }}</p>
                <p class="mt-2 text-stone-600">{{ __('Coba kata kunci lain atau hapus sebagian saringan.') }}</p>
            </div>
        @else
            <h2 class="sr-only">{{ __('Hasil pencarian') }}</h2>
            <div class="mt-4 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($statues as $statue)
                    <x-statue-card :statue="$statue" />
                @endforeach
            </div>

            <div class="mt-10">
                {{ $statues->links('partials.pagination') }}
            </div>
        @endif
    </div>
</x-layouts.app>
