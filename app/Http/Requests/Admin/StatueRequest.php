<?php

namespace App\Http\Requests\Admin;

use App\Enums\StatueStatus;
use App\Models\Theme;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StatueRequest extends FormRequest
{
    /**
     * Same bounding box the CSV importer uses, so a typo cannot drop a statue in the sea or on Java.
     */
    public const LATITUDE = [-8.90, -8.05];

    public const LONGITUDE = [114.40, 115.75];

    private const MAX_SOURCES = 10;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150', function (string $attribute, mixed $value, \Closure $fail) {
                if (Str::slug((string) $value) === '') {
                    $fail('Nama harus memuat huruf latin atau angka agar bisa dijadikan alamat halaman.');
                }
            }],
            'regency_id' => ['required', 'integer', Rule::exists('regencies', 'id')],
            'district' => ['required', 'string', 'max:100'],
            'village' => ['nullable', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:'.implode(',', self::LATITUDE)],
            'longitude' => ['required', 'numeric', 'between:'.implode(',', self::LONGITUDE)],
            'junction_type_id' => ['nullable', 'integer', Rule::exists('junction_types', 'id')],
            'junction_arms' => ['nullable', 'integer', 'between:3,8'],
            'description_id' => ['required', 'string', 'max:5000'],
            'description_en' => ['nullable', 'string', 'max:5000'],
            'sources' => ['nullable', 'array', 'max:'.self::MAX_SOURCES],
            'sources.*.title' => ['nullable', 'string', 'max:200'],
            'sources.*.url' => ['nullable', 'url:http,https', 'max:500'],
            'proposed_themes' => ['nullable', 'array'],
            'proposed_themes.*' => ['string', Rule::in(Theme::query()->pluck('slug')->all())],
            'proposal_note' => ['nullable', 'string', 'max:300'],
            'status' => ['required', Rule::enum(StatueStatus::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'max.string' => ':attribute maksimal :max karakter.',
            'max.array' => 'Maksimal :max :attribute.',
            'numeric' => ':attribute harus berupa angka.',
            'integer' => ':attribute harus berupa angka bulat.',
            'exists' => 'Pilihan :attribute tidak valid.',
            'in' => 'Pilihan :attribute tidak valid.',
            'enum' => 'Pilihan :attribute tidak valid.',
            'url' => ':attribute harus berupa tautan yang diawali http:// atau https://.',
            'latitude.between' => 'Latitude harus di antara :min dan :max (wilayah Bali).',
            'longitude.between' => 'Longitude harus di antara :min dan :max (wilayah Bali).',
            'junction_arms.between' => 'Jumlah arah harus di antara :min dan :max.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama',
            'regency_id' => 'Kabupaten/kota',
            'district' => 'Kecamatan',
            'village' => 'Desa/kelurahan',
            'junction_type_id' => 'Jenis persimpangan',
            'junction_arms' => 'Jumlah arah',
            'description_id' => 'Deskripsi (Indonesia)',
            'description_en' => 'Deskripsi (Inggris)',
            'sources' => 'sumber',
            'sources.*.title' => 'Judul sumber',
            'sources.*.url' => 'Tautan sumber',
            'proposed_themes.*' => 'usulan tema',
            'proposal_note' => 'Catatan usulan',
            'status' => 'Status',
        ];
    }

    /**
     * Validated input shaped for the Statue model.
     *
     * @return array<string, mixed>
     */
    public function statueAttributes(): array
    {
        $data = $this->validated();
        $themes = Theme::query()->orderBy('sort_order')->pluck('slug');

        return [
            'name' => trim($data['name']),
            'regency_id' => (int) $data['regency_id'],
            'district' => trim($data['district']),
            'village' => filled($data['village'] ?? null) ? trim($data['village']) : null,
            'latitude' => round((float) $data['latitude'], 7),
            'longitude' => round((float) $data['longitude'], 7),
            'junction_type_id' => filled($data['junction_type_id'] ?? null) ? (int) $data['junction_type_id'] : null,
            'junction_arms' => filled($data['junction_arms'] ?? null) ? (int) $data['junction_arms'] : null,
            'description' => array_filter([
                'id' => trim($data['description_id']),
                'en' => trim((string) ($data['description_en'] ?? '')),
            ]),
            'sources' => collect($data['sources'] ?? [])
                ->filter(fn (array $source) => filled($source['title'] ?? null) || filled($source['url'] ?? null))
                ->map(fn (array $source) => [
                    'title' => filled($source['title'] ?? null)
                        ? trim($source['title'])
                        : (string) (parse_url((string) $source['url'], PHP_URL_HOST) ?: $source['url']),
                    'url' => filled($source['url'] ?? null) ? trim($source['url']) : null,
                ])
                ->values()
                ->all(),
            'proposed_themes' => $themes->intersect($data['proposed_themes'] ?? [])->values()->all() ?: null,
            'proposal_note' => filled($data['proposal_note'] ?? null) ? trim($data['proposal_note']) : null,
            'status' => StatueStatus::from($data['status']),
        ];
    }
}
