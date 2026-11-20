@props(['href', 'external' => false])

<a
    href="{{ $href }}"
    @if ($external) target="_blank" rel="noopener noreferrer" @endif
    {{ $attributes->class('font-medium text-brand-700 underline decoration-brand-200 underline-offset-4 transition hover:text-brand-800 hover:decoration-brand-600') }}
>{{ $slot }}@if ($external)<span class="sr-only"> ({{ __('membuka tab baru') }})</span>@endif</a>
