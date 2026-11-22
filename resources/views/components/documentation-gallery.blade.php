@props(['title', 'photos', 'tile' => 'aspect-[4/3]', 'featureFirst' => false])

@php
    $urls = array_map(fn (array $photo) => asset("images/dokumentasi/{$photo['file']}"), $photos);
    $captions = array_column($photos, 'caption');
@endphp

<div data-gallery data-photos="{{ json_encode($urls) }}" data-photo-captions="{{ json_encode($captions) }}" {{ $attributes->class('grid grid-cols-2 gap-3 sm:gap-4') }}>
    @foreach ($photos as $index => $photo)
        <figure @class(['col-span-full' => $featureFirst && $index === 0])>
            <button type="button" data-gallery-open="{{ $index }}" class="group block w-full cursor-zoom-in overflow-hidden rounded-2xl bg-stone-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">
                <img src="{{ asset("images/dokumentasi/thumbs/{$photo['file']}") }}" alt="{{ $photo['caption'] }}" loading="lazy" @class(['w-full object-cover transition duration-500 group-hover:scale-[1.03]', $featureFirst && $index === 0 ? 'aspect-[16/9]' : $tile])>
            </button>
            <figcaption class="mt-2 text-sm leading-snug text-stone-600">{{ $photo['caption'] }}</figcaption>
        </figure>
    @endforeach

    <x-photo-dialog :title="$title" :urls="$urls" />
</div>
