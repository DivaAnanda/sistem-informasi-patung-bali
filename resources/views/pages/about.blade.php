<x-layouts.app :title="__('Tentang proyek')">
    <article class="mx-auto max-w-3xl px-4 pt-10 sm:px-6">
        @include('pages.about.'.app()->getLocale())
    </article>
</x-layouts.app>
