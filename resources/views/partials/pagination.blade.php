@if ($paginator->hasPages())
    <nav aria-label="{{ __('Navigasi halaman') }}" class="flex items-center justify-between gap-4 border-t border-stone-200 pt-6">
        @if ($paginator->onFirstPage())
            <span class="text-sm text-stone-400">← {{ __('Sebelumnya') }}</span>
        @else
            <x-text-link :href="$paginator->previousPageUrl()" rel="prev" class="text-sm">← {{ __('Sebelumnya') }}</x-text-link>
        @endif

        <p class="text-sm text-stone-600">
            {{ __('Halaman :current dari :last', ['current' => $paginator->currentPage(), 'last' => $paginator->lastPage()]) }}
        </p>

        @if ($paginator->hasMorePages())
            <x-text-link :href="$paginator->nextPageUrl()" rel="next" class="text-sm">{{ __('Berikutnya') }} →</x-text-link>
        @else
            <span class="text-sm text-stone-400">{{ __('Berikutnya') }} →</span>
        @endif
    </nav>
@endif
