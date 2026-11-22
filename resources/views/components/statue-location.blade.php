@props(['statue', 'marker'])

<div {{ $attributes->class('space-y-4') }}>
    <dl class="divide-y divide-stone-200 rounded-2xl border border-stone-200 bg-white text-sm">
        @foreach ([
            __('Kabupaten/kota') => $statue->regency->name,
            __('Kecamatan') => $statue->district,
            __('Desa/kelurahan') => $statue->village ?? '–',
            __('Tema') => $statue->themes->isNotEmpty() ? $statue->themes->pluck('name')->implode(', ') : __('Menunggu klasifikasi informan'),
            __('Jenis persimpangan') => $statue->junctionLabel() ?? __('Belum diverifikasi'),
        ] as $term => $value)
            <div class="flex justify-between gap-4 px-5 py-3">
                <dt class="text-stone-600">{{ $term }}</dt>
                <dd class="text-right font-medium">{{ $value }}</dd>
            </div>
        @endforeach
        <div class="flex justify-between gap-4 px-5 py-3">
            <dt class="text-stone-600">{{ __('Koordinat') }}</dt>
            <dd class="text-right font-mono text-xs leading-5">{{ number_format($statue->latitude, 6) }}, {{ number_format($statue->longitude, 6) }}</dd>
        </div>
    </dl>

    @if ($guardian = $statue->junctionGuardian())
        <p class="rounded-2xl bg-brand-50 px-5 py-4 text-sm leading-relaxed text-brand-800">
            {{ __('Menurut Tutur Gong Besi, persimpangan seperti ini dijaga :guardian. Sesajen di sini ditujukan kepada penjaga ruang itu, bukan kepada patungnya.', ['guardian' => $guardian]) }}
        </p>
    @endif

    <x-statue-map id="statue-map" :markers="[$marker]" class="h-72" />

    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1">
        <x-button-link :href="$statue->directionsUrl()" external>{{ __('Petunjuk arah') }}</x-button-link>
        <x-button-link :href="$statue->googleMapsUrl()" variant="secondary" external>{{ __('Buka di Google Maps') }}</x-button-link>
    </div>
</div>
