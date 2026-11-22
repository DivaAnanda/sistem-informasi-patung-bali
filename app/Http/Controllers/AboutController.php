<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.about', [
            'themes' => Theme::query()->orderBy('sort_order')->get(),
        ]);
    }
}
