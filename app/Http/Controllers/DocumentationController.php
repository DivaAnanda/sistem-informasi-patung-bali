<?php

namespace App\Http\Controllers;

use App\Models\Statue;
use Illuminate\View\View;

class DocumentationController extends Controller
{
    private const PUPUTAN_BADUNG_NO = 83;

    /**
     * Full names in the order the team stands in the photo at Monumen Puputan Badung, left to right.
     * The caption names them once this list is filled in.
     *
     * @var list<string>
     */
    private const TEAM_LEFT_TO_RIGHT = [];

    public function __invoke(): View
    {
        return view('pages.documentation', [
            'puputan' => Statue::query()->firstWhere('source_no', self::PUPUTAN_BADUNG_NO),
            'team' => self::TEAM_LEFT_TO_RIGHT,
        ]);
    }
}
