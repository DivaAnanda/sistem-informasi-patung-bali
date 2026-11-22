@props(['statue', 'distance' => null])

<article class="group relative flex flex-col overflow-hidden rounded-2xl border border-stone-200 bg-white transition focus-within:ring-2 focus-within:ring-brand-600 hover:border-brand-200 hover:shadow-lg hover:shadow-stone-900/5">
    <div class="relative aspect-[4/3] overflow-hidden">
        <x-statue-photo :statue="$statue" decorative class="transition duration-500 group-hover:scale-[1.03]" />
        <span class="absolute top-3 left-3 rounded-full bg-paper/90 px-2.5 py-1 text-xs font-medium text-ink shadow-sm backdrop-blur">{{ $statue->regency->name }}</span>
    </div>

    <div class="flex flex-1 flex-col gap-4 p-5">
        <div>
            <h3 class="font-display text-lg leading-snug font-semibold text-ink">
                <a href="{{ route('statues.show', $statue) }}" class="after:absolute after:inset-0 focus:outline-none">{{ $statue->name }}</a>
            </h3>
            <p class="mt-1 text-sm text-stone-600">{{ $statue->place() }}</p>
        </div>

        <div class="mt-auto flex flex-wrap gap-2">
            @if ($distance !== null)
                <x-badge tone="brand">{{ __(':distance km dari sini', ['distance' => Number::format($distance, precision: 1, locale: app()->getLocale())]) }}</x-badge>
            @endif
            @foreach ($statue->themes as $theme)
                <x-badge tone="brand">{{ $theme->name }}</x-badge>
            @endforeach
            @if ($junction = $statue->junctionLabel())
                <x-badge>{{ $junction }}</x-badge>
            @endif
        </div>
    </div>
</article>
