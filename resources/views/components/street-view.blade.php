@props(['statue'])

@php
    $apiKey = config('services.google_maps.embed_key');
    $backdrop = $statue->thumbnailUrl();
@endphp

<section aria-labelledby="street-view-heading" {{ $attributes }}>
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 id="street-view-heading" class="font-display text-2xl font-semibold sm:text-3xl">{{ __('Lihat sekeliling patung') }}</h2>
            <p class="mt-1 text-stone-600">{{ __('Putar 360° dan susuri jalan di sekitarnya lewat Google Street View.') }}</p>
        </div>
        <x-text-link :href="$statue->streetViewUrl()" external class="text-sm">{{ __('Buka di Google Maps') }}</x-text-link>
    </div>

    <div data-street-view class="relative mt-5 aspect-[4/3] overflow-hidden rounded-2xl border border-stone-200 bg-ink sm:aspect-[21/9]">
        @php
            $label = $apiKey ? __('Tampilkan Street View 360°') : __('Buka Street View 360°');
        @endphp

        @if ($apiKey)
            <button type="button" data-street-view-load="{{ $statue->streetViewEmbedUrl($apiKey) }}" data-street-view-title="{{ __('Street View di sekitar :name', ['name' => $statue->name]) }}" class="group absolute inset-0 grid place-items-center">
        @else
            <a href="{{ $statue->streetViewUrl() }}" target="_blank" rel="noopener noreferrer" class="group absolute inset-0 grid place-items-center">
        @endif

            @if ($backdrop)
                <img src="{{ $backdrop }}" alt="" loading="lazy" class="absolute inset-0 h-full w-full object-cover opacity-40 blur-sm transition duration-500 group-hover:scale-105 group-hover:opacity-50">
            @endif
            <span class="relative flex flex-col items-center gap-3 px-6 text-center text-paper">
                <span class="grid size-16 place-items-center rounded-full bg-paper/15 ring-1 ring-paper/30 backdrop-blur transition group-hover:bg-paper/25">
                    <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3z" /></svg>
                </span>
                <span class="font-display text-xl font-semibold">{{ $label }}</span>
                <span class="text-sm text-stone-300">
                    {{ $apiKey ? __('Klik untuk memuat panorama di halaman ini.') : __('Terbuka di Google Maps, tab baru.') }}
                </span>
            </span>

        @if ($apiKey)
            </button>
        @else
            <span class="sr-only">({{ __('membuka tab baru') }})</span>
            </a>
        @endif
    </div>

    <p class="mt-2 text-xs text-stone-500">{{ __('Panorama milik Google dan kontributornya; titik panorama terdekat bisa berjarak beberapa meter dari patung.') }}</p>
</section>
