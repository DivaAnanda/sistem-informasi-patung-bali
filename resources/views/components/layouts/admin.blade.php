@props(['title' => null])

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ? $title.' · ' : '' }}Admin {{ config('app.name') }}</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-stone-100 font-sans text-ink antialiased">
        @if (\App\Support\Admin::active())
            <header class="border-b border-stone-200 bg-white">
                <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6">
                    <a href="{{ route('admin.statues.index') }}" class="flex items-center gap-2.5">
                        <x-logo-mark class="size-7 text-brand-700" />
                        <span class="font-display text-lg font-semibold">Admin {{ config('app.name') }}</span>
                    </a>
                    <nav class="flex items-center gap-2 text-sm">
                        <a href="{{ route('admin.statues.index') }}" class="rounded-full px-3 py-1.5 hover:bg-stone-100">Daftar patung</a>
                        <a href="{{ route('admin.statues.create') }}" class="rounded-full bg-brand-600 px-3 py-1.5 font-medium text-white hover:bg-brand-700">+ Tambah patung</a>
                        <a href="{{ route('home', ['locale' => 'id']) }}" class="rounded-full px-3 py-1.5 hover:bg-stone-100" target="_blank" rel="noopener">Lihat website</a>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="rounded-full px-3 py-1.5 text-stone-600 hover:bg-stone-100">Keluar</button>
                        </form>
                    </nav>
                </div>
            </header>
        @endif

        <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
            @if (session('status'))
                <p role="status" class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</p>
            @endif

            {{ $slot }}
        </main>
    </body>
</html>
