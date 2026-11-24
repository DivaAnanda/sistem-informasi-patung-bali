<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Statue;
use App\Services\StatueImport\StatueImporter;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Backup of the whole catalog in the importer's column order, plus a status column at the end.
 */
class ExportController extends Controller
{
    private const NOT_A_JUNCTION = [
        'pinggir-jalan' => 'Bukan persimpangan (pinggir jalan)',
        'dalam-kawasan' => 'Bukan persimpangan (di dalam kawasan)',
    ];

    public function __invoke(): StreamedResponse
    {
        $statues = Statue::withUnpublished()->with(['regency', 'themes', 'junctionType'])->orderBy('source_no')->get();

        return response()->streamDownload(function () use ($statues) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [...StatueImporter::COLUMNS, 'status'], escape: '');

            foreach ($statues as $statue) {
                fputcsv($out, $this->row($statue), escape: '');
            }

            fclose($out);
        }, 'patung-bali-'.now('Asia/Makassar')->format('Y-m-d-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return list<string>
     */
    private function row(Statue $statue): array
    {
        $values = [
            'no' => $statue->source_no,
            'name' => $statue->name,
            'regency' => $statue->regency->name,
            'district' => $statue->district,
            'village' => $statue->village,
            'latitude' => number_format($statue->latitude, 7, '.', ''),
            'longitude' => number_format($statue->longitude, 7, '.', ''),
            'themes' => $statue->themes->sortBy('sort_order')->pluck('slug')->implode('; '),
            'proposed_themes' => implode('; ', $statue->proposed_themes ?? []),
            'proposal_note' => $statue->proposal_note,
            'junction_type' => $this->junctionLabel($statue),
            'description_id' => $statue->getTranslation('description', 'id', false),
            'description_en' => $statue->getTranslation('description', 'en', false),
            'sources' => collect($statue->sources ?? [])
                ->map(fn (array $source) => $source['url'] ? "{$source['title']} [{$source['url']}]" : $source['title'])
                ->implode('; '),
            'photos' => implode(';', $statue->photos ?? []),
        ];

        return [...array_map(fn (string $column) => (string) ($values[$column] ?? ''), StatueImporter::COLUMNS), $statue->status->value];
    }

    private function junctionLabel(Statue $statue): string
    {
        $slug = $statue->junctionType?->slug;

        if ($slug === null) {
            return 'Perlu verifikasi';
        }

        return self::NOT_A_JUNCTION[$slug]
            ?? ucfirst($slug).($statue->junction_arms ? " ({$statue->junction_arms} arah)" : '');
    }
}
