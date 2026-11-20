@props(['statue', 'decorative' => false])

@if ($statue->photoUrl())
    <img
        src="{{ $statue->photoUrl() }}"
        alt="{{ $decorative ? '' : $statue->name }}"
        loading="lazy"
        {{ $attributes->class('h-full w-full object-cover') }}
    >
@else
    @php
        $initial = Str::of($statue->name)->replaceMatches('/^(patung|arca)\s+/i', '')->substr(0, 1)->upper();
    @endphp
    <div {{ $attributes->class('relative flex h-full w-full items-center justify-center overflow-hidden bg-gradient-to-br from-brand-100 via-paper to-stone-200') }} aria-hidden="true">
        <x-logo-mark class="absolute -right-6 -bottom-8 size-40 text-brand-200/60" />
        <span class="relative font-display text-6xl font-semibold text-brand-700/80">{{ $initial }}</span>
    </div>
@endif
