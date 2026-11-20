<div class="flex items-center rounded-full border border-stone-300 bg-white p-0.5 text-xs font-semibold" role="group" aria-label="{{ __('Pilih bahasa') }}">
    @foreach (\App\Support\Locales::available() as $locale)
        <a
            href="{{ \App\Support\Locales::currentUrlIn($locale) }}"
            hreflang="{{ $locale }}"
            lang="{{ $locale }}"
            aria-label="{{ \App\Support\Locales::name($locale) }}"
            @if ($locale === app()->getLocale()) aria-current="true" @endif
            @class([
                'rounded-full px-3 py-1.5 uppercase tracking-wide transition',
                'bg-ink text-paper' => $locale === app()->getLocale(),
                'text-stone-600 hover:text-ink' => $locale !== app()->getLocale(),
            ])
        >{{ $locale }}</a>
    @endforeach
</div>
