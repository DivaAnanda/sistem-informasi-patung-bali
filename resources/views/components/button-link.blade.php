@props(['href', 'variant' => 'primary', 'external' => false])

<a
    href="{{ $href }}"
    @if ($external) target="_blank" rel="noopener noreferrer" @endif
    {{ $attributes->class([
        'inline-flex h-11 items-center justify-center gap-2 rounded-full px-5 text-sm font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600',
        'bg-ink text-paper hover:bg-brand-800' => $variant === 'primary',
        'border border-stone-300 bg-white text-ink hover:border-stone-500' => $variant === 'secondary',
    ]) }}
>{{ $slot }}@if ($external)<span class="sr-only"> ({{ __('membuka tab baru') }})</span>@endif</a>
