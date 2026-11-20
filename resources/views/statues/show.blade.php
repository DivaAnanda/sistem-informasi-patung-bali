@php
    $hasDescription = filled($statue->description);
    $showsFallbackNotice = $hasDescription && app()->getLocale() === 'en' && ! $statue->hasEnglishDescription();
    $paragraphs = $hasDescription ? preg_split('/\R+/', trim($statue->description)) : [];
@endphp

<x-layouts.app :title="$statue->name" :description="$hasDescription ? Str::limit($statue->description, 155) : null">
    <article class="mx-auto max-w-6xl px-4 pt-8 sm:px-6">
        <nav aria-label="{{ __('Jejak navigasi') }}" class="text-sm text-stone-600">
            <ol class="flex flex-wrap items-center gap-2">
                <li><a href="{{ route('statues.index') }}" class="hover:text-ink">{{ __('Jelajahi') }}</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('statues.index', ['kabupaten' => $statue->regency->slug]) }}" class="hover:text-ink">{{ $statue->regency->name }}</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-ink">{{ $statue->name }}</li>
            </ol>
        </nav>

        <header class="mt-6 max-w-3xl">
            <h1 class="font-display text-3xl leading-tight font-semibold tracking-tight sm:text-5xl">{{ $statue->name }}</h1>
            <p class="mt-3 text-lg text-stone-700">{{ $statue->place() }}</p>
            <div class="mt-4 flex flex-wrap gap-2">
                @if ($statue->statueType)
                    <x-badge tone="brand">{{ $statue->statueType->name }}</x-badge>
                @endif
                @if ($junction = $statue->junctionLabel())
                    <x-badge>{{ $junction }}</x-badge>
                @endif
            </div>
        </header>

        <div class="mt-10 grid gap-10 lg:grid-cols-[minmax(0,1fr)_22rem]">
            <div>
                <x-statue-gallery :statue="$statue" />

                <section class="mt-12" aria-labelledby="story-heading">
                    <h2 id="story-heading" class="font-display text-2xl font-semibold">{{ __('Kisah dan makna') }}</h2>

                    @if ($showsFallbackNotice)
                        <p class="mt-4 rounded-xl bg-brand-50 px-4 py-3 text-sm text-brand-800">
                            {{ __('Deskripsi ini baru tersedia dalam Bahasa Indonesia.') }}
                        </p>
                    @endif

                    @if ($hasDescription)
                        <div class="mt-4 max-w-prose space-y-4 text-lg leading-relaxed text-stone-800" @if ($showsFallbackNotice) lang="id" @endif>
                            @foreach ($paragraphs as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-4 text-stone-600">{{ __('Deskripsi patung ini belum tersedia.') }}</p>
                    @endif
                </section>

                @if (filled($statue->sources))
                    <section class="mt-12" aria-labelledby="sources-heading">
                        <h2 id="sources-heading" class="font-display text-2xl font-semibold">{{ __('Sumber rujukan') }}</h2>
                        <ol class="mt-4 list-decimal space-y-2 pl-5 text-stone-700 marker:text-stone-400">
                            @foreach ($statue->sources as $source)
                                <li>
                                    @if ($source['url'])
                                        <x-text-link :href="$source['url']" external>{{ $source['title'] }}</x-text-link>
                                    @else
                                        {{ $source['title'] }}
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </section>
                @endif

                @if ($review)
                    <section class="mt-12" aria-labelledby="location-heading">
                        <h2 id="location-heading" class="font-display text-2xl font-semibold">{{ __('Lokasi') }}</h2>
                        <x-statue-location :statue="$statue" :marker="$marker" class="mt-4 max-w-xl" />
                    </section>
                @endif
            </div>

            @if ($review)
                <aside class="order-first lg:sticky lg:top-32 lg:order-none lg:self-start">
                    <x-review-panel :statue="$statue" :review="$review" />
                </aside>
            @else
                <aside class="lg:self-start" aria-label="{{ __('Informasi lokasi') }}">
                    <x-statue-location :statue="$statue" :marker="$marker" />
                </aside>
            @endif
        </div>

        @if ($nearby->isNotEmpty())
            <section class="mt-20" aria-labelledby="nearby-heading">
                <h2 id="nearby-heading" class="font-display text-2xl font-semibold sm:text-3xl">{{ __('Patung terdekat') }}</h2>
                <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($nearby as $item)
                        <x-statue-card :statue="$item['statue']" :distance="$item['km']" />
                    @endforeach
                </div>
            </section>
        @endif
    </article>
</x-layouts.app>
