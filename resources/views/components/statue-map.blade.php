@props(['id', 'markers', 'scrollZoom' => false, 'zoom' => null])

<div
    {{ $attributes->class('isolate overflow-hidden rounded-2xl border border-stone-200 bg-stone-100') }}
    data-statue-map
    data-markers="{{ $id }}-data"
    data-scroll-zoom="{{ $scrollZoom ? 'true' : 'false' }}"
    @if ($zoom) data-zoom="{{ $zoom }}" @endif
    role="region"
    aria-label="{{ __('Peta lokasi patung') }}"
></div>
<script type="application/json" id="{{ $id }}-data">@json($markers)</script>
