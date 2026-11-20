@props(['statue'])

@php
    $urls = $statue->photoUrls();
@endphp

<figure data-gallery>
    <div class="aspect-[16/10] overflow-hidden rounded-2xl border border-stone-200 bg-stone-100">
        @if ($urls)
            <a href="{{ $urls[0] }}" target="_blank" rel="noopener" data-gallery-link class="block h-full">
                <img src="{{ $urls[0] }}" alt="{{ $statue->name }}" class="h-full w-full object-contain" data-gallery-main>
                <span class="sr-only">({{ __('membuka tab baru') }})</span>
            </a>
        @else
            <x-statue-photo :statue="$statue" />
        @endif
    </div>

    @if (count($urls) > 1)
        <div class="mt-3 flex gap-2 overflow-x-auto pb-1">
            @foreach ($urls as $index => $url)
                <button
                    type="button"
                    data-gallery-thumb="{{ $url }}"
                    aria-pressed="{{ $index === 0 ? 'true' : 'false' }}"
                    aria-label="{{ __('Foto :number', ['number' => $index + 1]) }}"
                    class="shrink-0 overflow-hidden rounded-lg border-2 border-transparent opacity-70 transition hover:opacity-100 aria-pressed:border-brand-600 aria-pressed:opacity-100"
                >
                    <img src="{{ $url }}" alt="" loading="lazy" class="h-16 w-24 object-cover">
                </button>
            @endforeach
        </div>
    @endif

    <figcaption class="mt-2 text-sm text-stone-500">
        @if ($urls)
            {{ $statue->photo_credit ?? __('Foto dokumentasi tim. Klik foto untuk melihat ukuran penuh.') }}
        @else
            {{ __('Foto lapangan sedang dikumpulkan oleh tim.') }}
        @endif
    </figcaption>
</figure>
