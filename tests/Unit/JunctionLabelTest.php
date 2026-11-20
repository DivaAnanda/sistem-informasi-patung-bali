<?php

namespace Tests\Unit;

use App\Services\StatueImport\JunctionLabel;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class JunctionLabelTest extends TestCase
{
    #[DataProvider('knownLabels')]
    public function test_parses_the_team_vocabulary(?string $label, ?string $expectedSlug, ?int $expectedArms): void
    {
        $parsed = JunctionLabel::parse($label);

        $this->assertSame($expectedSlug, $parsed->typeSlug);
        $this->assertSame($expectedArms, $parsed->arms);
    }

    /**
     * @return array<string, array{0: ?string, 1: ?string, 2: ?int}>
     */
    public static function knownLabels(): array
    {
        return [
            'crossroads' => ['Perempatan (4 arah)', 'perempatan', 4],
            't-junction' => ['Pertigaan (3 arah)', 'pertigaan', 3],
            'roundabout with odd spacing and case' => ['  bundaran  (5 arah) ', 'bundaran', 5],
            'roundabout without arm count' => ['Bundaran', 'bundaran', null],
            'multi-way junction' => ['Simpang (5 arah)', 'simpang', 5],
            'crossroads without arm count' => ['Perempatan', 'perempatan', null],
            'roadside' => ['Bukan persimpangan (pinggir jalan)', 'pinggir-jalan', null],
            'inside a site' => ['Bukan persimpangan (di dalam kawasan)', 'dalam-kawasan', null],
            'needs verification' => ['Perlu verifikasi', null, null],
            'empty' => ['', null, null],
            'missing' => [null, null, null],
        ];
    }

    public function test_rejects_labels_outside_the_vocabulary(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Simpang lima');

        JunctionLabel::parse('Simpang lima');
    }
}
