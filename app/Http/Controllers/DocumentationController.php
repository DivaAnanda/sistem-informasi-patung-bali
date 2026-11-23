<?php

namespace App\Http\Controllers;

use App\Models\Statue;
use Illuminate\View\View;

class DocumentationController extends Controller
{
    private const PUPUTAN_BADUNG_NO = 83;

    /**
     * Group 1 of the Digital Cultural Heritage course. Listed as in the team's progress report, not in
     * the order they stand in the photos.
     *
     * @var list<string>
     */
    private const TEAM = [
        'I Gede Surya Diva Ananda',
        'I Ketut Tangkas Agus Sucita',
        'Putu Krisna Udayana',
        'I Kadek Dwika Pradnyana',
        'I Gede Parama Sathiyam Yuda Yana',
    ];

    public function __invoke(): View
    {
        return view('pages.documentation', [
            'puputan' => Statue::query()->firstWhere('source_no', self::PUPUTAN_BADUNG_NO),
            'team' => self::TEAM,
        ]);
    }
}
