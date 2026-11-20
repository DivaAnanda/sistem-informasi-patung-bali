<?php

use App\Http\Controllers\ExpertSessionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewExportController;
use App\Http\Controllers\StatueController;
use App\Http\Middleware\EnsureExpert;
use App\Http\Middleware\SetLocale;
use App\Support\Locales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn (Request $request) => redirect()->route('home', [
    'locale' => $request->getPreferredLanguage(Locales::available()),
]));

Route::get('/ekspor-validasi/{token}', ReviewExportController::class)
    ->middleware('throttle:10,1')
    ->name('reviews.export');

Route::prefix('{locale}')
    ->where(['locale' => Locales::pattern()])
    ->middleware(SetLocale::class)
    ->group(function () {
        Route::get('/', HomeController::class)->name('home');
        Route::get('/patung', [StatueController::class, 'index'])->name('statues.index');
        Route::get('/patung/{statue}', [StatueController::class, 'show'])->name('statues.show');
        Route::get('/peta', MapController::class)->name('map');
        Route::view('/tentang', 'pages.about')->name('about');

        Route::get('/validasi/masuk/{token}', [ExpertSessionController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('expert.login');
        Route::post('/validasi/keluar', [ExpertSessionController::class, 'destroy'])->name('expert.logout');

        Route::middleware(EnsureExpert::class)->group(function () {
            Route::get('/validasi', [ReviewController::class, 'index'])->name('reviews.index');
            Route::put('/validasi/{statue}', [ReviewController::class, 'update'])->name('reviews.update');
        });
    });
