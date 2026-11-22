@php
    $links = [
        ['route' => 'home', 'label' => __('Beranda'), 'active' => request()->routeIs('home')],
        ['route' => 'statues.index', 'label' => __('Jelajahi'), 'active' => request()->routeIs('statues.*')],
        ['route' => 'map', 'label' => __('Peta'), 'active' => request()->routeIs('map')],
        ['route' => 'documentation', 'label' => __('Dokumentasi'), 'active' => request()->routeIs('documentation')],
        ['route' => 'about', 'label' => __('Tentang'), 'active' => request()->routeIs('about')],
    ];
@endphp

<header class="sticky top-0 z-40 border-b border-stone-200/80 bg-paper/90 backdrop-blur">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 rounded-full focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-600">
            <x-logo-mark class="size-8 text-brand-700" />
            <span class="font-display text-lg font-semibold tracking-tight">{{ config('app.name') }}</span>
        </a>

        <nav aria-label="{{ __('Navigasi utama') }}" class="hidden md:block">
            <ul class="flex items-center gap-1">
                @foreach ($links as $link)
                    <li>
                        <a
                            href="{{ route($link['route']) }}"
                            @if ($link['active']) aria-current="page" @endif
                            @class([
                                'rounded-full px-4 py-2 text-sm font-medium transition',
                                'bg-ink text-paper' => $link['active'],
                                'text-stone-700 hover:bg-stone-200/60 hover:text-ink' => ! $link['active'],
                            ])
                        >{{ $link['label'] }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="flex items-center gap-2">
            <x-locale-switcher />

            <details class="group relative md:hidden">
                <summary class="grid size-10 cursor-pointer list-none place-items-center rounded-full border border-stone-300 bg-white [&::-webkit-details-marker]:hidden">
                    <span class="sr-only">{{ __('Menu') }}</span>
                    <svg class="size-5 group-open:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
                    <svg class="hidden size-5 group-open:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" /></svg>
                </summary>
                <nav aria-label="{{ __('Navigasi utama') }}" class="absolute right-0 mt-2 w-56 rounded-2xl border border-stone-200 bg-white p-2 shadow-xl shadow-stone-900/10">
                    @foreach ($links as $link)
                        <a
                            href="{{ route($link['route']) }}"
                            @if ($link['active']) aria-current="page" @endif
                            @class([
                                'block rounded-xl px-4 py-3 text-sm font-medium',
                                'bg-brand-50 text-brand-800' => $link['active'],
                                'text-stone-700 hover:bg-stone-100' => ! $link['active'],
                            ])
                        >{{ $link['label'] }}</a>
                    @endforeach
                </nav>
            </details>
        </div>
    </div>

    @if (\App\Support\Expert::active())
        <div class="border-t border-brand-200 bg-brand-50">
            <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-x-4 gap-y-1 px-4 py-2 text-sm sm:px-6">
                <p class="font-medium text-brand-800">{{ __('Mode klasifikasi informan aktif') }}</p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('reviews.index') }}" class="font-semibold text-brand-800 underline underline-offset-4">{{ __('Daftar klasifikasi') }}</a>
                    <form method="POST" action="{{ route('expert.logout') }}">
                        @csrf
                        <button type="submit" class="text-brand-800 hover:underline">{{ __('Keluar') }}</button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</header>
