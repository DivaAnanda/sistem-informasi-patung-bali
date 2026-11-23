@php
    $informant = 'Dewa Gede Purwita, S.Pd., M.Sn.';
    $groupCaption = __('Tim di depan Monumen Puputan Badung.');
    if ($team !== []) {
        $groupCaption .= ' '.__('Anggota tim: :names.', ['names' => implode(', ', $team)]);
    }

    $visit = [
        ['file' => 'kunjungan-puputan-1.jpg', 'caption' => $groupCaption],
        ['file' => 'kunjungan-puputan-2.jpg', 'caption' => __('Menuju monumen di Lapangan Puputan Badung.')],
        ['file' => 'kunjungan-puputan-3.jpg', 'caption' => __('Tim di Lapangan Puputan Badung, dekat Pasar Badung.')],
    ];

    $interview = [
        ['file' => 'wawancara-1.jpg', 'caption' => __('Informan :name bersama tim.', ['name' => $informant])],
        ['file' => 'wawancara-2.jpg', 'caption' => __('Membahas klasifikasi tema patung bersama informan.')],
        ['file' => 'wawancara-3.jpg', 'caption' => __('Catatan struktur tema yang ditulis selama wawancara.')],
        ['file' => 'wawancara-sketsa.jpg', 'caption' => __('Sketsa informan: patung landmark, lalu tema-temanya.')],
    ];

    $kicker = 'text-sm font-semibold tracking-[0.18em] text-brand-700 uppercase';
@endphp

<x-layouts.app :title="__('Dokumentasi')" :description="__('Dokumentasi kegiatan tim Patung Bali: kunjungan langsung ke patung dan wawancara dengan informan budaya.')">
    <div class="mx-auto max-w-6xl px-4 pt-10 pb-4 sm:px-6 lg:pt-14">
        <p class="{{ $kicker }}">{{ __('Dokumentasi') }}</p>
        <h1 class="mt-3 max-w-3xl font-display text-4xl leading-[1.1] font-semibold tracking-tight sm:text-5xl">{{ __('Dari lapangan ke website') }}</h1>
        <p class="mt-5 max-w-2xl text-lg leading-relaxed text-stone-700">
            {{ __('Data di website ini tidak hanya dikumpulkan dari internet. Tim mendatangi patung secara langsung dan mewawancarai informan budaya untuk menyusun klasifikasinya. Berikut catatan kegiatannya.') }}
        </p>
    </div>

    <ol class="mx-auto max-w-6xl space-y-20 px-4 pt-10 sm:px-6">
        <li>
            <section aria-labelledby="visit-heading" class="grid gap-8 lg:grid-cols-[20rem_1fr]">
                <div class="lg:sticky lg:top-24 lg:self-start">
                    <p class="{{ $kicker }}"><time datetime="2026-11-20">{{ __('20 November 2026') }}</time> · Denpasar</p>
                    <h2 id="visit-heading" class="mt-3 font-display text-2xl leading-snug font-semibold sm:text-3xl">{{ __('Kunjungan ke Monumen Puputan Badung') }}</h2>
                    <p class="mt-4 leading-relaxed text-stone-700">
                        {{ __('Tim mendatangi Monumen Puputan Badung di Lapangan Puputan Badung I Gusti Ngurah Made Agung, dekat Pasar Badung, untuk melihat dan mendokumentasikan patungnya secara langsung.') }}
                    </p>
                    @if ($puputan)
                        <x-text-link :href="route('statues.show', $puputan)" class="mt-4 inline-block text-sm">{{ __('Lihat halaman patungnya') }} →</x-text-link>
                    @endif
                </div>

                <x-documentation-gallery :title="__('Kunjungan ke Monumen Puputan Badung')" :photos="$visit" feature-first />
            </section>
        </li>

        <li>
            <section aria-labelledby="interview-heading" class="grid gap-8 lg:grid-cols-[20rem_1fr]">
                <div class="lg:sticky lg:top-24 lg:self-start">
                    <p class="{{ $kicker }}"><time datetime="2026-11-20">{{ __('20 November 2026') }}</time></p>
                    <h2 id="interview-heading" class="mt-3 font-display text-2xl leading-snug font-semibold sm:text-3xl">{{ __('Wawancara dengan informan budaya') }}</h2>
                    <p class="mt-4 leading-relaxed text-stone-700">
                        {{ __('Tim mewawancarai :name, dosen Institut Desain dan Bisnis Bali (IDB Bali), untuk menyusun klasifikasi patung. Hasilnya dipakai di website ini: semua patung dipandang sebagai patung landmark, lalu dibedakan menurut temanya.', ['name' => $informant]) }}
                    </p>
                    <x-text-link :href="route('about')" class="mt-4 inline-block text-sm">{{ __('Baca daftar tema di halaman Tentang') }} →</x-text-link>
                </div>

                <div class="space-y-10">
                    <x-documentation-gallery :title="__('Wawancara dengan informan budaya')" :photos="$interview" tile="aspect-square" class="sm:grid-cols-3" feature-first />

                    <figure class="grid items-center gap-6 rounded-3xl bg-ink p-5 text-paper sm:grid-cols-[14rem_1fr] sm:p-6">
                        <video controls playsinline preload="none" poster="{{ asset('images/dokumentasi/demo-website-informan.jpg') }}" class="aspect-[9/16] w-full rounded-2xl bg-black object-cover">
                            <source src="{{ asset('images/dokumentasi/demo-website-informan.mp4') }}" type="video/mp4">
                        </video>
                        <figcaption>
                            <p class="font-display text-xl font-semibold">{{ __('Demo website kepada informan') }}</p>
                            <p class="mt-2 leading-relaxed text-stone-300">{{ __('Tim menunjukkan rancangan website ini kepada informan saat wawancara.') }}</p>
                        </figcaption>
                    </figure>
                </div>
            </section>
        </li>
    </ol>
</x-layouts.app>
