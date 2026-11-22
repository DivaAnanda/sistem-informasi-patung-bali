<x-layouts.app>
    <x-parallax-hero />

    <section class="mx-auto max-w-6xl px-4 pt-10 pb-16 sm:px-6 lg:pt-16">
        <div class="grid items-center gap-10 lg:grid-cols-[1fr_1.1fr]">
            <div>
                <p class="text-sm font-semibold tracking-[0.18em] text-brand-700 uppercase">{{ __('Warisan budaya digital · Bali') }}</p>
                <h2 class="mt-4 font-display text-4xl leading-[1.08] font-semibold tracking-tight sm:text-5xl lg:text-6xl">
                    {{ __('Patung-patung yang menjaga persimpangan Bali') }}
                </h2>
                <p class="mt-6 max-w-xl text-lg leading-relaxed text-stone-700">
                    {{ __('Kenali kisah di balik patung yang Anda lewati: tokoh yang digambarkan, cerita yang melatarinya, dan di mana tepatnya patung itu berdiri.') }}
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <x-button-link :href="route('statues.index')">{{ __('Jelajahi patung') }}</x-button-link>
                    <x-button-link :href="route('map')" variant="secondary">{{ __('Buka peta') }}</x-button-link>
                </div>

                <dl class="mt-10 grid max-w-lg grid-cols-3 gap-6 border-t border-stone-200 pt-6">
                    <div>
                        <dt class="text-sm text-stone-600">{{ __('Patung terdata') }}</dt>
                        <dd class="mt-1 font-display text-3xl font-semibold">{{ $statueCount }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-stone-600">{{ __('Kabupaten/kota') }}</dt>
                        <dd class="mt-1 font-display text-3xl font-semibold">{{ $coveredRegencyCount }}<span class="text-lg text-stone-500"> / {{ $regencyCount }}</span></dd>
                    </div>
                    <div>
                        <dt class="text-sm text-stone-600">{{ __('Foto') }}</dt>
                        <dd class="mt-1 font-display text-3xl font-semibold">{{ $photoCount }}</dd>
                    </div>
                </dl>
            </div>

            @if ($hero->isNotEmpty())
                <div class="grid h-[26rem] grid-cols-2 grid-rows-2 gap-3 sm:h-[32rem]">
                    @foreach ($hero as $index => $statue)
                        <a href="{{ route('statues.show', $statue) }}" @class([
                            'group relative overflow-hidden rounded-2xl bg-stone-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600',
                            'row-span-2' => $index === 0,
                        ])>
                            <img src="{{ $index === 0 ? $statue->photoUrl() : $statue->thumbnailUrl() }}" alt="" class="h-full w-full object-cover transition duration-700 group-hover:scale-105" loading="lazy">
                            <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink/80 via-ink/30 to-transparent px-4 pt-10 pb-3">
                                <span class="block font-display text-base leading-tight font-semibold text-paper sm:text-lg">{{ $statue->name }}</span>
                                <span class="block text-xs text-stone-200">{{ $statue->regency->name }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @if ($regencyTiles->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 sm:px-6" aria-labelledby="regency-heading">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 id="regency-heading" class="font-display text-2xl font-semibold sm:text-3xl">{{ __('Jelajahi per kabupaten') }}</h2>
                    <p class="mt-1 text-stone-600">{{ __('Delapan kabupaten dan satu kota, masing-masing dengan patung khasnya.') }}</p>
                </div>
                <x-text-link :href="route('statues.index')" class="text-sm">{{ __('Lihat semua patung') }} →</x-text-link>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
                @foreach ($regencyTiles as $tile)
                    <a href="{{ route('statues.index', ['kabupaten' => $tile['regency']->slug]) }}" class="group relative aspect-[4/3] overflow-hidden rounded-2xl bg-stone-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">
                        @if ($tile['cover'])
                            <img src="{{ $tile['cover'] }}" alt="" loading="lazy" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
                        @endif
                        <span class="absolute inset-0 bg-gradient-to-t from-ink/85 via-ink/20 to-transparent"></span>
                        <span class="absolute inset-x-0 bottom-0 p-4">
                            <span class="block font-display text-lg leading-tight font-semibold text-paper sm:text-xl">{{ $tile['regency']->name }}</span>
                            <span class="block text-sm text-stone-200">{{ trans_choice(':count patung', $tile['count'], ['count' => $tile['count']]) }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if ($featured->isNotEmpty())
        <section class="mx-auto mt-20 max-w-6xl px-4 sm:px-6" aria-labelledby="featured-heading">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <h2 id="featured-heading" class="font-display text-2xl font-semibold sm:text-3xl">{{ __('Dari koleksi') }}</h2>
                    <p class="mt-1 text-stone-600">{{ __('Pilihan berganti setiap hari.') }}</p>
                </div>
            </div>

            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $statue)
                    <x-statue-card :statue="$statue" />
                @endforeach
            </div>
        </section>
    @endif

    <section class="mx-auto mt-20 max-w-6xl px-4 sm:px-6" aria-labelledby="map-heading">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 id="map-heading" class="font-display text-2xl font-semibold sm:text-3xl">{{ __('Peta persebaran') }}</h2>
                <p class="mt-1 text-stone-600">{{ __('Klik penanda untuk melihat foto dan membuka halaman patungnya.') }}</p>
            </div>
            <x-text-link :href="route('map')" class="text-sm">{{ __('Buka peta penuh') }} →</x-text-link>
        </div>
        <x-statue-map id="home-map" :markers="$markers" class="mt-6 h-[26rem] sm:h-[32rem]" />
    </section>

    <section class="mx-auto mt-20 max-w-6xl px-4 sm:px-6">
        <div class="grid gap-8 rounded-3xl bg-ink px-6 py-10 text-paper sm:px-10 sm:py-14 md:grid-cols-2">
            <h2 class="font-display text-2xl leading-snug font-semibold sm:text-3xl">{{ __('Mengapa patung persimpangan?') }}</h2>
            <div class="space-y-5 leading-relaxed text-stone-300">
                <p>{{ __('Di Bali, patung besar di persimpangan bukan sekadar penghias kota. Banyak di antaranya menghadirkan tokoh Ramayana, Mahabharata, dan mitologi lokal, sekaligus menjadi penanda arah bagi warga dan wisatawan.') }}</p>
                <p>{{ __('Informasi tentang tokoh, pematung, dan sejarahnya selama ini tersebar di berita, jurnal, dan dokumen pemerintah. Website ini mengumpulkannya di satu tempat.') }}</p>
                <a href="{{ route('about') }}" class="inline-block font-semibold text-brand-200 underline decoration-brand-200/40 underline-offset-4 hover:text-white">{{ __('Tentang proyek ini') }} →</a>
            </div>
        </div>
    </section>
</x-layouts.app>
