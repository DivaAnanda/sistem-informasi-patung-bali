@props(['title' => null, 'description' => null])

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ? $title.' · '.config('app.name') : config('app.name') }}</title>
        <meta name="description" content="{{ $description ?? __('Dokumentasi digital patung di persimpangan dan ruang publik Bali: tokoh, kisah, dan lokasinya.') }}">

        @if (\App\Support\Expert::active())
            <meta name="csrf-token" content="{{ csrf_token() }}">
            <meta name="robots" content="noindex, nofollow">
        @endif

        @foreach (\App\Support\Locales::available() as $locale)
            <link rel="alternate" hreflang="{{ $locale }}" href="{{ \App\Support\Locales::currentUrlIn($locale) }}">
        @endforeach

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-paper font-sans text-ink antialiased">
        <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded-full focus:bg-ink focus:px-4 focus:py-2 focus:text-paper">
            {{ __('Lewati ke konten') }}
        </a>

        <x-site-header />

        <main id="main">
            {{ $slot }}
        </main>

        <x-site-footer />
    </body>
</html>
