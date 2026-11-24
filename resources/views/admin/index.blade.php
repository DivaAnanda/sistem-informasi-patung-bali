<x-layouts.admin title="Kelola patung">
    @php
        $statusStyles = [
            'published' => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
            'draft' => 'bg-amber-50 text-amber-800 ring-amber-200',
            'hidden' => 'bg-stone-100 text-stone-700 ring-stone-300',
        ];
    @endphp

    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl font-semibold">Kelola patung</h1>
            <p class="mt-1 text-sm text-stone-600">
                {{ $counts->sum() }} patung · {{ $counts['published'] ?? 0 }} terbit · {{ $counts['draft'] ?? 0 }} draf · {{ $counts['hidden'] ?? 0 }} disembunyikan
            </p>
        </div>
        <a href="{{ route('admin.export') }}" class="rounded-full border border-stone-300 bg-white px-4 py-2 text-sm hover:bg-stone-50">Unduh CSV cadangan</a>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <label class="sr-only" for="q">Cari</label>
        <input id="q" name="q" value="{{ $search }}" placeholder="Cari nama, kecamatan, desa, atau nomor"
               class="min-w-64 flex-1 rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm">
        <label class="sr-only" for="status">Status</label>
        <select id="status" name="status" class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm">
            <option value="">Semua status</option>
            @foreach (\App\Enums\StatueStatus::cases() as $case)
                <option value="{{ $case->value }}" @selected($status === $case)>{{ $case->label() }}</option>
            @endforeach
        </select>
        <button class="rounded-full bg-ink px-4 py-2 text-sm text-white">Terapkan</button>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-stone-200 bg-stone-50 text-xs uppercase tracking-wide text-stone-500">
                <tr>
                    <th class="px-4 py-3">No</th>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Lokasi</th>
                    <th class="px-4 py-3">Tema</th>
                    <th class="px-4 py-3">Foto</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($statues as $statue)
                    <tr class="hover:bg-stone-50">
                        <td class="px-4 py-3 tabular-nums text-stone-500">{{ $statue->source_no }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.statues.edit', $statue->id) }}" class="font-medium text-brand-700 hover:underline">{{ $statue->name }}</a>
                        </td>
                        <td class="px-4 py-3 text-stone-600">{{ $statue->district }}, {{ $statue->regency->name }}</td>
                        <td class="px-4 py-3 text-stone-600">
                            {{ $statue->themes->isEmpty() ? 'Menunggu informan' : $statue->themes->map->getTranslation('name', 'id')->implode(', ') }}
                        </td>
                        <td class="px-4 py-3 tabular-nums text-stone-600">{{ count($statue->photos ?? []) }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 {{ $statusStyles[$statue->status->value] }}">{{ $statue->status->label() }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-stone-500">Tidak ada patung yang cocok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.admin>
