@php
    $editing = $statue->exists;
    $input = 'w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:border-brand-600 focus:outline-none';
    $label = 'mb-1 block text-sm font-medium';
    $card = 'rounded-2xl border border-stone-200 bg-white p-5 sm:p-6';
    $sources = old('sources', $statue->sources ?? []);
    $sources = array_pad(array_values($sources), max(3, count($sources) + 1), ['title' => '', 'url' => '']);
    $proposed = old('proposed_themes', $statue->proposed_themes ?? []);
    $currentStatus = old('status', $statue->status?->value ?? 'published');
@endphp

<x-layouts.admin :title="$editing ? $statue->name : 'Tambah patung'">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <a href="{{ route('admin.statues.index') }}" class="text-sm text-stone-500 hover:underline">&larr; Daftar patung</a>
            <h1 class="mt-1 font-display text-2xl font-semibold">{{ $editing ? $statue->name : 'Tambah patung' }}</h1>
            @if ($editing)
                <p class="mt-1 text-sm text-stone-600">
                    No {{ $statue->source_no }} ·
                    @if ($statue->status === \App\Enums\StatueStatus::Published)
                        <a href="{{ route('statues.show', ['locale' => 'id', 'statue' => $statue]) }}" class="text-brand-700 hover:underline" target="_blank" rel="noopener">Lihat halaman publik</a>
                    @else
                        {{ $statue->status->label() }} (tidak tampil di website)
                    @endif
                    · Tema: {{ $statue->themes->isEmpty() ? 'menunggu informan' : $statue->themes->map->getTranslation('name', 'id')->implode(', ') }}
                </p>
            @endif
        </div>
    </div>

    @if ($errors->any() && ! $errors->has('photo'))
        <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-medium">Periksa lagi isian berikut:</p>
            <ul class="mt-1 list-disc pl-5">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $editing ? route('admin.statues.update', $statue->id) : route('admin.statues.store') }}" class="space-y-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <section class="{{ $card }}">
            <h2 class="mb-4 font-semibold">Data patung</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="{{ $label }}">Nama patung</label>
                    <input id="name" name="name" value="{{ old('name', $statue->name) }}" required maxlength="150" class="{{ $input }}">
                    @unless ($editing)
                        <p class="mt-1 text-xs text-stone-500">Alamat halaman dibuat dari nama ini dan tidak berubah walau nama diedit nanti.</p>
                    @endunless
                </div>
                <div>
                    <label for="regency_id" class="{{ $label }}">Kabupaten/kota</label>
                    <select id="regency_id" name="regency_id" required class="{{ $input }}">
                        <option value="">Pilih</option>
                        @foreach ($regencies as $regency)
                            <option value="{{ $regency->id }}" @selected((int) old('regency_id', $statue->regency_id) === $regency->id)>{{ $regency->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="district" class="{{ $label }}">Kecamatan</label>
                    <input id="district" name="district" value="{{ old('district', $statue->district) }}" required maxlength="100" class="{{ $input }}">
                </div>
                <div>
                    <label for="village" class="{{ $label }}">Desa/kelurahan <span class="font-normal text-stone-500">(opsional)</span></label>
                    <input id="village" name="village" value="{{ old('village', $statue->village) }}" maxlength="100" class="{{ $input }}">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="junction_type_id" class="{{ $label }}">Jenis persimpangan</label>
                        <select id="junction_type_id" name="junction_type_id" class="{{ $input }}">
                            <option value="">Belum diketahui</option>
                            @foreach ($junctionTypes as $type)
                                <option value="{{ $type->id }}" @selected((int) old('junction_type_id', $statue->junction_type_id) === $type->id)>{{ $type->getTranslation('name', 'id') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="junction_arms" class="{{ $label }}">Jumlah arah</label>
                        <input id="junction_arms" name="junction_arms" type="number" min="3" max="8" value="{{ old('junction_arms', $statue->junction_arms) }}" class="{{ $input }}">
                    </div>
                </div>
            </div>
        </section>

        <section class="{{ $card }}">
            <h2 class="mb-1 font-semibold">Lokasi</h2>
            <p class="mb-4 text-sm text-stone-600">Klik peta atau geser penanda ke posisi patung. Koordinat terisi otomatis.</p>
            <div data-admin-map data-lat-input="latitude" data-lng-input="longitude" class="mb-4 h-80 overflow-hidden rounded-xl border border-stone-200"></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="latitude" class="{{ $label }}">Latitude</label>
                    <input id="latitude" name="latitude" inputmode="decimal" value="{{ old('latitude', $statue->latitude) }}" required class="{{ $input }}">
                </div>
                <div>
                    <label for="longitude" class="{{ $label }}">Longitude</label>
                    <input id="longitude" name="longitude" inputmode="decimal" value="{{ old('longitude', $statue->longitude) }}" required class="{{ $input }}">
                </div>
            </div>
        </section>

        <section class="{{ $card }}">
            <h2 class="mb-4 font-semibold">Kisah dan makna</h2>
            <div class="space-y-4">
                <div>
                    <label for="description_id" class="{{ $label }}">Deskripsi (Indonesia)</label>
                    <textarea id="description_id" name="description_id" rows="6" required maxlength="5000" class="{{ $input }}">{{ old('description_id', $editing ? $statue->getTranslation('description', 'id', false) : '') }}</textarea>
                </div>
                <div>
                    <label for="description_en" class="{{ $label }}">Deskripsi (Inggris) <span class="font-normal text-stone-500">(opsional; kosong berarti halaman Inggris memakai teks Indonesia)</span></label>
                    <textarea id="description_en" name="description_en" rows="4" maxlength="5000" class="{{ $input }}">{{ old('description_en', $editing ? $statue->getTranslation('description', 'en', false) : '') }}</textarea>
                </div>
            </div>
        </section>

        <section class="{{ $card }}">
            <h2 class="mb-1 font-semibold">Sumber rujukan</h2>
            <p class="mb-4 text-sm text-stone-600">Isi judul dan tautan. Baris kosong diabaikan; simpan dulu untuk menambah baris baru.</p>
            <div class="space-y-3">
                @foreach ($sources as $i => $source)
                    <div class="grid gap-2 sm:grid-cols-[1fr_1.5fr]">
                        <input name="sources[{{ $i }}][title]" value="{{ $source['title'] ?? '' }}" placeholder="Judul, mis. Antara Bali (28 Okt 2011)" maxlength="200" class="{{ $input }}" aria-label="Judul sumber {{ $i + 1 }}">
                        <input name="sources[{{ $i }}][url]" value="{{ $source['url'] ?? '' }}" placeholder="https://" maxlength="500" class="{{ $input }}" aria-label="Tautan sumber {{ $i + 1 }}">
                    </div>
                @endforeach
            </div>
        </section>

        <section class="{{ $card }}">
            <h2 class="mb-1 font-semibold">Usulan tema untuk informan</h2>
            <p class="mb-4 text-sm text-stone-600">Hanya saran untuk informan. Tema yang tampil di website tetap pilihan informan.</p>
            <div class="mb-4 flex flex-wrap gap-2">
                @foreach ($themes as $theme)
                    <label class="flex items-center gap-2 rounded-full border border-stone-300 px-3 py-1.5 text-sm has-checked:border-brand-600 has-checked:bg-brand-50">
                        <input type="checkbox" name="proposed_themes[]" value="{{ $theme->slug }}" @checked(in_array($theme->slug, $proposed, true)) class="accent-brand-600">
                        {{ $theme->getTranslation('name', 'id') }}
                    </label>
                @endforeach
            </div>
            <label for="proposal_note" class="{{ $label }}">Alasan usulan <span class="font-normal text-stone-500">(opsional)</span></label>
            <input id="proposal_note" name="proposal_note" value="{{ old('proposal_note', $statue->proposal_note) }}" maxlength="300" class="{{ $input }}">
        </section>

        <section class="{{ $card }}">
            <h2 class="mb-4 font-semibold">Status</h2>
            <div class="flex flex-wrap gap-3">
                @foreach ($statuses as $case)
                    <label class="flex items-center gap-2 rounded-lg border border-stone-300 px-3 py-2 text-sm has-checked:border-brand-600 has-checked:bg-brand-50">
                        <input type="radio" name="status" value="{{ $case->value }}" @checked($currentStatus === $case->value) class="accent-brand-600">
                        <span><span class="font-medium">{{ $case->label() }}</span>
                            <span class="block text-xs text-stone-500">{{ match ($case) {
                                \App\Enums\StatueStatus::Published => 'Tampil di website dan di panel informan',
                                \App\Enums\StatueStatus::Draft => 'Belum tampil, masih dilengkapi',
                                \App\Enums\StatueStatus::Hidden => 'Diturunkan dari website, data tetap disimpan',
                            } }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </section>

        <div class="flex justify-end">
            <button class="rounded-full bg-brand-600 px-6 py-2.5 font-medium text-white hover:bg-brand-700">{{ $editing ? 'Simpan perubahan' : 'Simpan patung' }}</button>
        </div>
    </form>

    @if ($editing)
        <section id="foto" class="{{ $card }} mt-8">
            <h2 class="mb-1 font-semibold">Foto</h2>
            <p class="mb-4 text-sm text-stone-600">Foto pertama menjadi sampul. Foto otomatis diperkecil dan data lokasi (GPS) di dalamnya dihapus. Format JPG, PNG, atau WebP.</p>

            @error('photo')
                <p role="alert" class="mb-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-800">{{ $message }}</p>
            @enderror

            @if (! empty($statue->photos))
                <ul class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($statue->photos as $i => $photo)
                        <li class="overflow-hidden rounded-xl border border-stone-200">
                            <img src="{{ \App\Support\StatuePhotos::thumbnailUrl($photo) }}" alt="Foto {{ $i + 1 }}" loading="lazy" class="aspect-[4/3] w-full object-cover">
                            <div class="flex items-center justify-between gap-2 px-3 py-2 text-xs">
                                @if ($i === 0)
                                    <span class="font-medium text-brand-700">Sampul</span>
                                @else
                                    <form method="POST" action="{{ route('admin.photos.cover', $statue->id) }}">
                                        @csrf
                                        <input type="hidden" name="photo" value="{{ $photo }}">
                                        <button class="text-stone-700 hover:underline">Jadikan sampul</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.photos.destroy', $statue->id) }}" onsubmit="return confirm('Hapus foto ini dari patung?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="photo" value="{{ $photo }}">
                                    <button class="text-red-700 hover:underline">Hapus</button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            <form method="POST" action="{{ route('admin.photos.store', $statue->id) }}" enctype="multipart/form-data" data-photo-upload class="flex flex-wrap items-center gap-3">
                @csrf
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required data-photo-input
                       class="text-sm file:mr-3 file:rounded-full file:border-0 file:bg-stone-200 file:px-4 file:py-2 file:text-sm">
                <button class="rounded-full bg-ink px-4 py-2 text-sm text-white" data-photo-submit>Unggah foto</button>
                <p class="w-full text-xs text-stone-500" data-photo-progress aria-live="polite"></p>
            </form>
        </section>
    @endif
</x-layouts.admin>
