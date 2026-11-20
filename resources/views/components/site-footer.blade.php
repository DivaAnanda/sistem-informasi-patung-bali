<footer class="mt-24 border-t border-stone-200 bg-white/60">
    <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-3">
        <div>
            <div class="flex items-center gap-2.5">
                <x-logo-mark class="size-7 text-brand-700" />
                <span class="font-display text-lg font-semibold">{{ config('app.name') }}</span>
            </div>
            <p class="mt-3 max-w-xs text-sm leading-relaxed text-stone-600">
                {{ __('Dokumentasi digital patung di persimpangan dan ruang publik Bali.') }}
            </p>
        </div>

        <div>
            <h2 class="text-sm font-semibold">{{ __('Proyek') }}</h2>
            <p class="mt-3 text-sm leading-relaxed text-stone-600">{{ __('Proyek akhir mata kuliah Warisan Budaya Digital.') }}</p>
            <x-text-link href="https://github.com/DivaAnanda/sistem-informasi-patung-bali" class="mt-2 inline-block text-sm" external>
                {{ __('Kode sumber di GitHub') }}
            </x-text-link>
        </div>

        <div>
            <h2 class="text-sm font-semibold">{{ __('Data dan atribusi') }}</h2>
            <p class="mt-3 text-sm leading-relaxed text-stone-600">
                {{ __('Peta © kontributor OpenStreetMap (ODbL). Setiap halaman patung mencantumkan sumber rujukannya.') }}
            </p>
        </div>
    </div>
</footer>
