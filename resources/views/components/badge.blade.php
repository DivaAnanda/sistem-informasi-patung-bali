@props(['tone' => 'neutral'])

<span {{ $attributes->class([
    'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset',
    'bg-brand-50 text-brand-800 ring-brand-200' => $tone === 'brand',
    'bg-stone-100 text-stone-700 ring-stone-200' => $tone === 'neutral',
]) }}>{{ $slot }}</span>
