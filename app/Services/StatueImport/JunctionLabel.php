<?php

namespace App\Services\StatueImport;

use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Parses the team's controlled vocabulary for "Jenis Persimpangan".
 */
final readonly class JunctionLabel
{
    private const UNVERIFIED = 'perlu verifikasi';

    private const NOT_A_JUNCTION = [
        'bukan persimpangan (pinggir jalan)' => 'pinggir-jalan',
        'bukan persimpangan (di dalam kawasan)' => 'dalam-kawasan',
    ];

    private function __construct(
        public ?string $typeSlug,
        public ?int $arms,
    ) {}

    public static function parse(?string $label): self
    {
        $normalized = Str::of($label ?? '')->squish()->lower()->value();

        if ($normalized === '' || $normalized === self::UNVERIFIED) {
            return new self(null, null);
        }

        if (preg_match('/^(perempatan|pertigaan|bundaran|simpang)(?:\s*\((\d+)\s*arah\))?$/', $normalized, $match)) {
            return new self($match[1], isset($match[2]) ? (int) $match[2] : null);
        }

        if (isset(self::NOT_A_JUNCTION[$normalized])) {
            return new self(self::NOT_A_JUNCTION[$normalized], null);
        }

        throw new InvalidArgumentException("Jenis persimpangan \"{$label}\" tidak dikenal.");
    }
}
