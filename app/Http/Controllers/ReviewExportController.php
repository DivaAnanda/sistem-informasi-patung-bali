<?php

namespace App\Http\Controllers;

use App\Models\Statue;
use App\Models\StatueReview;
use App\Services\ReviewBoard;
use App\Support\Expert;
use Illuminate\Support\Facades\App;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The team's download of the informant's work. Reached only through a secret link the informant never sees.
 */
class ReviewExportController extends Controller
{
    private const HEADERS = [
        'No', 'Nama Patung', 'Kabupaten/Kota', 'Kecamatan', 'Desa/Kelurahan',
        'Usulan Tim: Tema', 'Dasar Usulan', 'Informan: Tema', 'Sesuai Usulan?', 'Catatan Informan',
        'Status', 'Terakhir Diubah (WITA)', 'Peringatan', 'Halaman',
    ];

    public function __invoke(string $token): StreamedResponse
    {
        abort_unless(Expert::tokenMatches(config('patung.export_token'), $token), 404);

        App::setLocale('id');
        $board = new ReviewBoard;
        $filename = 'klasifikasi-informan-'.now('Asia/Makassar')->format('Y-m-d-His').'.xlsx';

        return response()->streamDownload(function () use ($board) {
            $writer = new Writer;
            $writer->openToFile('php://output');
            $writer->addRow(Row::fromValuesWithStyle(self::HEADERS, (new Style)->withFontBold(true)));

            foreach ($board->statues() as $statue) {
                $writer->addRow(Row::fromValues($this->row($statue, $board->reviewFor($statue), $board)));
            }

            $writer->close();
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /**
     * @return list<int|string>
     */
    private function row(Statue $statue, ?StatueReview $review, ReviewBoard $board): array
    {
        $reviewed = (bool) $review?->isReviewed();
        $proposed = $statue->proposed_themes ?? [];
        $chosen = $review?->themes ?? [];
        $matches = $reviewed && collect($chosen)->sort()->values()->all() === collect($proposed)->sort()->values()->all();

        return [
            $statue->source_no,
            $statue->name,
            $statue->regency->name,
            $statue->district,
            $statue->village ?? '',
            $board->themeLabel($proposed) ?? '',
            $statue->proposal_note ?? '',
            $board->themeLabel($chosen) ?? '',
            $reviewed ? ($matches ? 'Ya' : 'Tidak') : '',
            $review?->notes ?? '',
            $reviewed ? 'Sudah diklasifikasi' : 'Belum diklasifikasi',
            $review?->updated_at?->timezone('Asia/Makassar')->format('Y-m-d H:i') ?? '',
            $review?->nameChangedFrom($statue) ? "Diklasifikasi saat bernama \"{$review->statue_name}\"; cek apakah nomor ini dipakai untuk patung lain." : '',
            route('statues.show', ['locale' => 'id', 'statue' => $statue]),
        ];
    }
}
