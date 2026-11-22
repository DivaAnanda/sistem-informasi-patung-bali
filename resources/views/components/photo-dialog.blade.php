@props(['title', 'urls'])

<dialog data-gallery-dialog aria-label="{{ $title }}" class="m-auto h-full max-h-none w-full max-w-none bg-transparent p-0 backdrop:bg-ink/90">
    <div class="flex h-full flex-col text-paper">
        <div class="flex items-center justify-between gap-4 px-4 py-3 sm:px-6">
            <p class="truncate text-sm"><span class="font-semibold">{{ $title }}</span> <span class="text-stone-400" data-gallery-counter aria-live="polite"></span></p>
            <div class="flex items-center gap-2">
                <a href="{{ $urls[0] }}" target="_blank" rel="noopener" data-gallery-original class="rounded-full px-3 py-1.5 text-xs font-medium text-stone-300 hover:bg-white/10 hover:text-white">{{ __('Buka file asli') }}</a>
                <button type="button" data-gallery-close class="grid size-10 place-items-center rounded-full hover:bg-white/10" aria-label="{{ __('Tutup') }}">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" /></svg>
                </button>
            </div>
        </div>
        <div class="relative flex min-h-0 flex-1 items-center justify-center px-2 pb-2 sm:px-16">
            <img src="{{ $urls[0] }}" alt="" class="max-h-full max-w-full object-contain" data-gallery-full>
            @if (count($urls) > 1)
                <button type="button" data-gallery-step="-1" class="absolute top-1/2 left-2 grid size-11 -translate-y-1/2 place-items-center rounded-full bg-white/10 hover:bg-white/20 sm:left-4" aria-label="{{ __('Foto sebelumnya') }}">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18l-6-6 6-6" /></svg>
                </button>
                <button type="button" data-gallery-step="1" class="absolute top-1/2 right-2 grid size-11 -translate-y-1/2 place-items-center rounded-full bg-white/10 hover:bg-white/20 sm:right-4" aria-label="{{ __('Foto berikutnya') }}">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 18l6-6-6-6" /></svg>
                </button>
            @endif
        </div>
        <p class="min-h-10 px-4 pb-4 text-center text-sm text-stone-300 sm:px-16" data-gallery-caption></p>
    </div>
</dialog>
