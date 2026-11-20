<x-layouts.app>
    <section class="mx-auto max-w-6xl px-4 pt-12 pb-16 sm:px-6 lg:pt-20">
        <div class="grid items-center gap-10 lg:grid-cols-[1.05fr_1fr]">
            <div>
                <p class="text-sm font-semibold tracking-[0.18em] text-brand-700 uppercase">{{ __('Warisan budaya digital · Bali') }}</p>
                <h1 class="mt-4 font-display text-4xl leading-[1.1] font-semibold tracking-tight sm:text-5xl lg:text-6xl">
                    {{ __('Patung-patung yang menjaga persimpangan Bali') }}
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-relaxed text-stone-700">
                    {{ __('Kenali kisah di balik patung yang Anda lewati: tokoh yang digambarkan, cerita yang melatarinya, dan di mana tepatnya patung itu berdiri.') }}
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <x-button-link :href="route('statues.index')">{{ __('Jelajahi patung') }}</x-button-link>
                    <x-button-link :href="route('map')" variant="secondary">{{ __('Buka peta') }}</x-button-link>
                </div>

                <dl class="mt-10 grid max-w-md grid-cols-2 gap-6 border-t border-stone-200 pt-6">
                    <div>
                        <dt class="text-sm text-stone-600">{{ __('Patung terdata') }}</dt>
                        <dd class="mt-1 font-display text-3xl font-semibold">{{ $statueCount }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-stone-600">{{ __('Kabupaten/kota') }}</dt>
                        <dd class="mt-1 font-display text-3xl font-semibold">
                            {{ $coveredRegencyCount }}<span class="text-lg text-stone-500"> / {{ $regencyCount }}</span>
                        </dd>
                    </div>
                </dl>
            </div>

            <x-statue-map id="home-map" :markers="$markers" class="h-80 sm:h-[28rem] lg:h-[32rem]" />
        </div>
    </section>

    @if ($featured->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 sm:px-6" aria-labelledby="featured-heading">
            <div class="flex items-end justify-between gap-4">
                <h2 id="featured-heading" class="font-display text-2xl font-semibold sm:text-3xl">{{ __('Dari koleksi') }}</h2>
                <x-text-link :href="route('statues.index')" class="text-sm">{{ __('Lihat semua patung') }} →</x-text-link>
            </div>

            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $statue)
                    <x-statue-card :statue="$statue" />
                @endforeach
            </div>
        </section>
    @endif

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
