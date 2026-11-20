<?php

namespace App\Services\StatueImport;

use App\Models\JunctionType;
use App\Models\Regency;
use App\Models\Statue;
use App\Models\StatueType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use InvalidArgumentException;
use SplFileObject;

/**
 * Imports statues from the cleaned dataset CSV. Every row is validated first;
 * nothing is written unless the whole file is valid.
 */
final class StatueImporter
{
    public const COLUMNS = [
        'no', 'name', 'regency', 'district', 'village', 'latitude', 'longitude',
        'statue_type', 'proposed_type', 'junction_type', 'description_id', 'description_en', 'sources', 'photos',
    ];

    private const ICONIC = 'ikonik';

    private const PHOTO_NAME = '/^[A-Za-z0-9_-]+\.(jpe?g|png|webp)$/';

    private const FIRST_DATA_ROW = 2;

    private const UTF8_BOM = "\xEF\xBB\xBF";

    /**
     * The CSV is the single source of truth: statues missing from it are removed.
     *
     * @return array{imported: int, removed: int}
     *
     * @throws StatueImportException
     */
    public function import(string $path): array
    {
        [$rows, $errors] = $this->readCsv($path);
        $lookups = $this->lookups();
        $records = [];

        foreach ($rows as $rowNumber => $row) {
            try {
                $records[] = $this->toRecord($row, $lookups);
            } catch (InvalidArgumentException $exception) {
                $errors[] = "Baris {$rowNumber}: {$exception->getMessage()}";
            }
        }

        $errors = [...$errors, ...$this->duplicateNumberErrors($records)];

        if ($rows === [] && $errors === []) {
            $errors[] = 'File tidak berisi baris data, jadi tidak ada yang diimpor.';
        }

        if ($errors !== []) {
            throw new StatueImportException($errors);
        }

        $removed = DB::transaction(fn () => $this->save($records));

        return ['imported' => count($records), 'removed' => $removed];
    }

    /**
     * @return array{0: array<int, array<string, string>>, 1: list<string>}
     */
    private function readCsv(string $path): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new StatueImportException(["File {$path} tidak ditemukan atau tidak bisa dibaca."]);
        }

        $file = new SplFileObject($path);

        if ($file->fread(strlen(self::UTF8_BOM)) !== self::UTF8_BOM) {
            $file->rewind();
        }

        $header = null;
        $rows = [];
        $errors = [];
        $rowNumber = self::FIRST_DATA_ROW - 1;

        while (($cells = $file->fgetcsv(',', '"', '')) !== false) {
            $isBlankLine = $cells === [null];

            if ($header === null) {
                if (! $isBlankLine) {
                    $header = $this->validatedHeader(array_map(fn ($cell) => trim((string) $cell), $cells));
                }

                continue;
            }

            $rowNumber++;

            if ($isBlankLine) {
                continue;
            }

            $cells = array_map(fn ($cell) => trim((string) $cell), $cells);

            if (count($cells) !== count($header)) {
                $errors[] = "Baris {$rowNumber}: jumlah kolom ".count($cells).', seharusnya '.count($header).'.';

                continue;
            }

            $rows[$rowNumber] = array_combine($header, $cells);
        }

        if ($header === null) {
            throw new StatueImportException(["File {$path} kosong."]);
        }

        return [$rows, $errors];
    }

    /**
     * @param  list<string>  $cells
     * @return list<string>
     */
    private function validatedHeader(array $cells): array
    {
        $missing = array_diff(self::COLUMNS, $cells);

        if ($missing !== []) {
            throw new StatueImportException(['Kolom wajib tidak ada: '.implode(', ', $missing).'.']);
        }

        return $cells;
    }

    /**
     * @return array{regencies: array<string, int>, statueTypes: array<string, int>, junctionTypes: array<string, int>}
     */
    private function lookups(): array
    {
        return [
            'regencies' => Regency::query()->pluck('id', 'slug')->all(),
            'statueTypes' => StatueType::query()->pluck('id', 'slug')->all(),
            'junctionTypes' => JunctionType::query()->pluck('id', 'slug')->all(),
        ];
    }

    /**
     * @param  array<string, string>  $row
     * @param  array{regencies: array<string, int>, statueTypes: array<string, int>, junctionTypes: array<string, int>}  $lookups
     * @return array<string, mixed>
     */
    private function toRecord(array $row, array $lookups): array
    {
        $this->validateRow($row);

        $regencyKey = Str::slug(preg_replace('/^(kabupaten|kab\.?|kota)\s+/i', '', $row['regency']));
        $statueTypeKey = Str::slug($row['statue_type']);
        $junction = JunctionLabel::parse($row['junction_type']);

        return [
            'source_no' => (int) $row['no'],
            'name' => $row['name'],
            'regency_id' => $lookups['regencies'][$regencyKey]
                ?? throw new InvalidArgumentException("Kabupaten \"{$row['regency']}\" tidak dikenal."),
            'district' => $row['district'],
            'village' => $row['village'] !== '' ? $row['village'] : null,
            'latitude' => (float) $row['latitude'],
            'longitude' => (float) $row['longitude'],
            'statue_type_id' => $statueTypeKey === '' ? null : ($lookups['statueTypes'][$statueTypeKey]
                ?? throw new InvalidArgumentException("Jenis patung \"{$row['statue_type']}\" tidak dikenal.")),
            'junction_type_id' => $junction->typeSlug === null ? null : ($lookups['junctionTypes'][$junction->typeSlug]
                ?? throw new InvalidArgumentException("Jenis persimpangan \"{$junction->typeSlug}\" belum ada di database.")),
            'junction_arms' => $junction->arms,
            'description' => array_filter(['id' => $row['description_id'], 'en' => $row['description_en']]),
            'sources' => $this->parseSources($row['sources']),
            'photos' => $this->parsePhotos($row['photos']),
            ...$this->parseProposal($row['proposed_type'], $lookups['statueTypes']),
        ];
    }

    /**
     * Accepts "Budaya", "Ikonik" or "Budaya + Ikonik": at most one main type, plus the Ikonik flag.
     *
     * @param  array<string, int>  $statueTypes
     * @return array{proposed_type_id: ?int, proposed_iconic: bool}
     */
    private function parseProposal(string $value, array $statueTypes): array
    {
        $keys = collect(explode('+', $value))->map(fn (string $part) => Str::slug($part))->filter();
        $mainTypes = $keys->reject(fn (string $key) => $key === self::ICONIC)->values();

        if ($mainTypes->count() > 1) {
            throw new InvalidArgumentException("Usulan jenis \"{$value}\" memuat lebih dari satu jenis utama.");
        }

        $mainType = $mainTypes->first();

        return [
            'proposed_type_id' => $mainType === null ? null : ($statueTypes[$mainType]
                ?? throw new InvalidArgumentException("Usulan jenis \"{$value}\" tidak dikenal.")),
            'proposed_iconic' => $keys->contains(self::ICONIC),
        ];
    }

    /**
     * @return list<string>
     */
    private function parsePhotos(string $value): array
    {
        $files = array_values(array_filter(array_map('trim', explode(';', $value))));

        foreach ($files as $file) {
            if (! preg_match(self::PHOTO_NAME, $file)) {
                throw new InvalidArgumentException("Nama file foto \"{$file}\" tidak valid.");
            }
        }

        return $files;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function validateRow(array $row): void
    {
        $validator = Validator::make($row, [
            'no' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:150'],
            'regency' => ['required', 'string'],
            'district' => ['required', 'string', 'max:100'],
            'village' => ['nullable', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:-8.90,-8.05'],
            'longitude' => ['required', 'numeric', 'between:114.40,115.75'],
            'description_id' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
        ], [
            'latitude.between' => 'Latitude :input berada di luar Bali.',
            'longitude.between' => 'Longitude :input berada di luar Bali.',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException(implode(' ', $validator->errors()->all()));
        }

        if (Str::slug($row['name']) === '') {
            throw new InvalidArgumentException("Nama \"{$row['name']}\" harus memuat huruf latin atau angka agar bisa dijadikan alamat halaman.");
        }
    }

    /**
     * Accepts "Judul [https://...]; Judul lain [https://...]". Semicolons inside the brackets belong to the URL.
     *
     * @return list<array{title: string, url: ?string}>
     */
    private function parseSources(string $value): array
    {
        return collect(preg_split('/;(?![^\[]*\])/', $value))
            ->map(fn (string $entry) => trim($entry))
            ->filter()
            ->map(function (string $entry): array {
                if (preg_match('/^(.*?)\s*\[(\S+)\]$/u', $entry, $match)) {
                    return ['title' => $match[1] !== '' ? $match[1] : $this->host($match[2]), 'url' => $this->webUrl($match[2])];
                }

                if (str_starts_with($entry, 'http')) {
                    return ['title' => $this->host($entry), 'url' => $this->webUrl($entry)];
                }

                return ['title' => $entry, 'url' => null];
            })
            ->values()
            ->all();
    }

    private function webUrl(string $url): string
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (filter_var($url, FILTER_VALIDATE_URL) === false || ! in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException("URL referensi \"{$url}\" tidak valid.");
        }

        return $url;
    }

    private function host(string $url): string
    {
        return (string) (parse_url($url, PHP_URL_HOST) ?: $url);
    }

    /**
     * @param  list<array<string, mixed>>  $records
     * @return list<string>
     */
    private function duplicateNumberErrors(array $records): array
    {
        return collect($records)
            ->countBy('source_no')
            ->filter(fn (int $count) => $count > 1)
            ->keys()
            ->map(fn (int $number) => "Nomor {$number} muncul lebih dari sekali.")
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $records
     * @return int Number of statues removed because they are no longer in the CSV.
     */
    private function save(array $records): int
    {
        $removed = Statue::query()->whereNotIn('source_no', array_column($records, 'source_no'))->delete();
        $slugs = Statue::query()->pluck('slug', 'source_no')->all();

        foreach ($records as $record) {
            $slug = $slugs[$record['source_no']] ?? $this->uniqueSlug($record, $slugs);
            $slugs[$record['source_no']] = $slug;

            Statue::query()->updateOrCreate(
                ['source_no' => $record['source_no']],
                [...$record, 'slug' => $slug],
            );
        }

        return $removed;
    }

    /**
     * Existing statues keep their slug so public URLs stay stable across imports.
     *
     * @param  array<string, mixed>  $record
     * @param  array<int, string>  $takenSlugs
     */
    private function uniqueSlug(array $record, array $takenSlugs): string
    {
        $base = Str::slug($record['name']);
        $candidates = [$base, $base.'-'.Str::slug($record['district'])];

        foreach ($candidates as $candidate) {
            if (! in_array($candidate, $takenSlugs, true)) {
                return $candidate;
            }
        }

        return $base.'-'.$record['source_no'];
    }
}
