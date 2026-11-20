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
 * The team's download of the expert's work. Reached only through a secret link the expert never sees.
 */
class ReviewExportController extends Controller
{
    private const HEADERS = [
        'No', 'Nama Patung', 'Kabupaten/Kota', 'Kecamatan', 'Desa/Kelurahan',
        'Usulan Tim: Jenis', 'Usulan Tim: Ikonik', 'Pakar: Jenis', 'Pakar: Ikonik',
        'Sesuai Usulan?', 'Catatan Pakar', 'Status', 'Terakhir Diubah (WITA)', 'Peringatan', 'Halaman',
    ];

    public function __invoke(string $token): StreamedResponse
    {
        abort_unless(Expert::tokenMatches(config('patung.export_token'), $token), 404);

        App::setLocale('id');
        $board = new ReviewBoard;
        $filename = 'validasi-pakar-'.now('Asia/Makassar')->format('Y-m-d-His').'.xlsx';

        return response()->streamDownload(function () use ($board) {
            $writer = new Writer;
            $writer->openToFile('php://output');
            $writer->addRow(Row::fromValuesWithStyle(self::HEADERS, (new Style)->withFontBold(true)));

            foreach ($board->statues() as $statue) {
                $writer->addRow(Row::fromValues($this->row($statue, $board->reviewFor($statue))));
            }

            $writer->close();
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /**
     * @return list<int|string>
     */
    private function row(Statue $statue, ?StatueReview $review): array
    {
        $reviewed = (bool) $review?->isReviewed();
        $matches = $reviewed
            && $review->statue_type === $statue->proposedType?->slug
            && $review->is_iconic === $statue->proposed_iconic;

        return [
            $statue->source_no,
            $statue->name,
            $statue->regency->name,
            $statue->district,
            $statue->village ?? '',
            $statue->proposedType?->name ?? '',
            $statue->proposed_iconic ? 'Ya' : 'Tidak',
            $review?->statue_type ? ucfirst($review->statue_type) : '',
            $review === null ? '' : ($review->is_iconic ? 'Ya' : 'Tidak'),
            $reviewed ? ($matches ? 'Ya' : 'Tidak') : '',
            $review?->notes ?? '',
            $reviewed ? 'Sudah diperiksa' : 'Belum diperiksa',
            $review?->updated_at?->timezone('Asia/Makassar')->format('Y-m-d H:i') ?? '',
            $review?->nameChangedFrom($statue) ? "Dinilai saat bernama \"{$review->statue_name}\"; cek apakah nomor ini dipakai untuk patung lain." : '',
            route('statues.show', ['locale' => 'id', 'statue' => $statue]),
        ];
    }
}
