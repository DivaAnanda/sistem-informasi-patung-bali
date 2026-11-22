@php
    // The 4:3 photo fills the width on wide screens, so crop from 40% down to keep both the statue and the team.
    $layer = 'pointer-events-none absolute inset-0 size-full object-cover object-[51%_40%] will-change-transform';
    $srcset = fn (string $name) => asset("images/hero/{$name}-1200.webp").' 1200w, '.asset("images/hero/{$name}-2400.webp").' 2400w';
    // On screens narrower than the 4:3 photo, object-cover scales it by height, so it renders wider than the viewport.
    $sizes = '(max-aspect-ratio: 4/3) calc((100vh - 4rem) * 4 / 3), 100vw';
@endphp

{{-- Four layers move at different speeds while scrolling: the sky slowest, then the title, then the
     monument, and the team with the page, so the title sinks behind the statue and then behind the team. --}}
<section data-parallax aria-labelledby="hero-title" class="relative isolate h-[calc(100svh-4rem)] min-h-[30rem] overflow-hidden bg-sky-200">
    <img data-parallax-layer="42" src="{{ asset('images/hero/hero-sky-1200.webp') }}" srcset="{{ $srcset('hero-sky') }}" sizes="{{ $sizes }}" width="2400" height="1800" alt="" class="{{ $layer }}">

    <div data-parallax-layer="28" class="pointer-events-none absolute inset-0 will-change-transform">
        {{-- "wide" means the photo spans the full width, which the side-by-side words are measured against. --}}
        <h1 id="hero-title" class="hero-title absolute inset-x-0 top-[4%] flex justify-center gap-[0.22em] font-display text-[15vw] leading-none font-semibold tracking-tight text-paper wide:top-[22%] wide:block wide:-translate-y-1/2 wide:text-[min(11vw,13rem)]">
            <span class="wide:absolute wide:right-[calc(48.5%+8.5vw)]">Patung</span>
            <span class="wide:absolute wide:left-[calc(51.5%+8.5vw)]">Bali</span>
        </h1>
    </div>

    <img data-parallax-layer="16" src="{{ asset('images/hero/hero-scene-1200.webp') }}" srcset="{{ $srcset('hero-scene') }}" sizes="{{ $sizes }}" width="2400" height="1800" fetchpriority="high" alt="" class="{{ $layer }}">

    <img data-parallax-layer="0" src="{{ asset('images/hero/hero-people-1200.webp') }}" srcset="{{ $srcset('hero-people') }}" sizes="{{ $sizes }}" width="2400" height="1800" fetchpriority="high"
        alt="{{ __('Tim Patung Bali berfoto di depan Monumen Puputan Badung, Denpasar.') }}" class="{{ $layer }}">

    <div class="pointer-events-none absolute inset-x-0 bottom-0 h-20 bg-gradient-to-b from-transparent to-paper sm:h-28" aria-hidden="true"></div>
</section>
