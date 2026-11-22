@props(['statue'])

@php
    $urls = $statue->photoUrls();
    $thumbs = $statue->thumbnailUrls();
@endphp

<figure data-gallery data-photos="{{ json_encode($urls) }}" data-photo-alt="{{ __('Foto :number dari :total, :name', ['number' => '__N__', 'total' => count($urls), 'name' => $statue->name]) }}">
    <div class="relative aspect-[16/10] overflow-hidden rounded-2xl border border-stone-200 bg-stone-100">
        @if ($urls)
            <button type="button" data-gallery-open class="group block h-full w-full cursor-zoom-in focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">
                <img src="{{ $urls[0] }}" alt="{{ $statue->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.02]" data-gallery-main>
                <span class="absolute right-3 bottom-3 inline-flex items-center gap-1.5 rounded-full bg-ink/75 px-3 py-1.5 text-xs font-medium text-paper backdrop-blur">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" /></svg>
                    {{ count($urls) > 1 ? __('Lihat :count foto', ['count' => count($urls)]) : __('Perbesar foto') }}
                </span>
            </button>
        @else
            <x-statue-photo :statue="$statue" />
        @endif
    </div>

    @if (count($urls) > 1)
        <div class="mt-3 flex gap-2 overflow-x-auto pb-1">
            @foreach ($thumbs as $index => $thumb)
                <button
                    type="button"
                    data-gallery-thumb="{{ $index }}"
                    aria-pressed="{{ $index === 0 ? 'true' : 'false' }}"
                    aria-label="{{ __('Foto :number', ['number' => $index + 1]) }}"
                    class="shrink-0 overflow-hidden rounded-lg border-2 border-transparent opacity-70 transition hover:opacity-100 aria-pressed:border-brand-600 aria-pressed:opacity-100"
                >
                    <img src="{{ $thumb }}" alt="" loading="lazy" class="h-16 w-24 object-cover">
                </button>
            @endforeach
        </div>
    @endif

    <figcaption class="mt-2 text-sm text-stone-500">
        @if ($urls)
            {{ $statue->photo_credit ?? __('Foto dokumentasi tim. Klik foto untuk melihat versi utuhnya.') }}
        @else
            {{ __('Foto lapangan sedang dikumpulkan oleh tim.') }}
        @endif
    </figcaption>

    @if ($urls)
        <x-photo-dialog :title="$statue->name" :urls="$urls" />
    @endif
</figure>
