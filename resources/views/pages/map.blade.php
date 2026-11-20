<x-layouts.app :title="__('Peta persebaran')">
    <div class="mx-auto max-w-6xl px-4 pt-10 sm:px-6">
        <header class="max-w-2xl">
            <h1 class="font-display text-3xl font-semibold sm:text-4xl">{{ __('Peta persebaran patung') }}</h1>
            <p class="mt-3 text-stone-700">
                {{ __(':count patung terpetakan. Ketuk penanda untuk melihat nama dan membuka halaman detailnya.', ['count' => $statueCount]) }}
            </p>
        </header>

        <x-statue-map id="full-map" :markers="$markers" :scroll-zoom="true" class="mt-6 h-[70vh] min-h-[28rem]" />

        <p class="mt-4 text-sm text-stone-600">
            {{ __('Lebih nyaman membaca daftar?') }}
            <x-text-link :href="route('statues.index')">{{ __('Buka daftar patung') }}</x-text-link>
        </p>
    </div>
</x-layouts.app>
