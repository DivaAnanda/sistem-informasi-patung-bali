<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Admin\ExportController as AdminExportController;
use App\Http\Controllers\Admin\SessionController as AdminSessionController;
use App\Http\Controllers\Admin\StatueController as AdminStatueController;
use App\Http\Controllers\Admin\StatuePhotoController as AdminStatuePhotoController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\ExpertSessionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewExportController;
use App\Http\Controllers\StatueController;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureExpert;
use App\Models\Statue;
use App\Http\Middleware\SetLocale;
use App\Support\Locales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn (Request $request) => redirect()->route('home', [
    'locale' => $request->getPreferredLanguage(Locales::available()),
]));

// The admin panel sees drafts and hidden statues too, and addresses them by id because slugs are public.
Route::bind('adminStatue', fn (string $value) => Statue::withUnpublished()->findOrFail((int) $value));

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/masuk', [AdminSessionController::class, 'create'])->name('login');
    Route::post('/masuk', [AdminSessionController::class, 'store'])->name('login.store');
    Route::post('/keluar', [AdminSessionController::class, 'destroy'])->name('logout');

    Route::middleware(EnsureAdmin::class)->group(function () {
        Route::get('/', [AdminStatueController::class, 'index'])->name('statues.index');
        Route::get('/patung/baru', [AdminStatueController::class, 'create'])->name('statues.create');
        Route::post('/patung', [AdminStatueController::class, 'store'])->name('statues.store');
        Route::get('/patung/{adminStatue}', [AdminStatueController::class, 'edit'])->whereNumber('adminStatue')->name('statues.edit');
        Route::put('/patung/{adminStatue}', [AdminStatueController::class, 'update'])->whereNumber('adminStatue')->name('statues.update');
        Route::post('/patung/{adminStatue}/foto', [AdminStatuePhotoController::class, 'store'])->whereNumber('adminStatue')->name('photos.store');
        Route::post('/patung/{adminStatue}/foto/sampul', [AdminStatuePhotoController::class, 'cover'])->whereNumber('adminStatue')->name('photos.cover');
        Route::delete('/patung/{adminStatue}/foto', [AdminStatuePhotoController::class, 'destroy'])->whereNumber('adminStatue')->name('photos.destroy');
        Route::get('/ekspor.csv', AdminExportController::class)->name('export');
    });
});

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
        Route::get('/dokumentasi', DocumentationController::class)->name('documentation');
        Route::get('/tentang', AboutController::class)->name('about');

        Route::get('/validasi/masuk/{token}', [ExpertSessionController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('expert.login');
        Route::post('/validasi/keluar', [ExpertSessionController::class, 'destroy'])->name('expert.logout');

        Route::middleware(EnsureExpert::class)->group(function () {
            Route::get('/validasi', [ReviewController::class, 'index'])->name('reviews.index');
            Route::put('/validasi/{statue}', [ReviewController::class, 'update'])->name('reviews.update');
        });
    });
