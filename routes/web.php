<?php

use App\Http\Controllers\Office\Finance\FinanceController;
use App\Http\Controllers\Office\GA\GAController;
use App\Http\Controllers\Office\HCM\HCMController;
use App\Http\Controllers\Office\ICC\ICCController;
use App\Http\Controllers\Office\QRD\QRDController;
use App\Http\Controllers\Office\OfficeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\School\SchoolController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
// public routes
Route::get('/', function () {
    return Inertia::render('Public/Index', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});
// portal routes
Route::get('portal', function () {
    return Inertia::render('Portal/Index');
})->middleware(['auth', 'verified'])->name('portal');
// office routes
Route::middleware(['auth', 'verified'])
    ->prefix('office')
    ->name('office')
    ->group(function () {
        Route::get('/', [OfficeController::class, 'index']);
        // my profile routes
        // icc routes
        Route::prefix('icc')
            ->name('.icc')
            ->group(function () {
                Route::get('/', [ICCController::class, 'index']);
                // management routes
                Route::prefix('management')
                    ->name('.management')
                    ->group(function () {
                        // admission stage routes
                        Route::prefix('admission-stage')
                            ->name('.admissionStage')
                            ->group(function () {
                                Route::get('/', [GAController::class, 'index']);
                            });
                    });
            });
        // hcm routes
        Route::prefix('hcm')
            ->name('.hcm')
            ->group(function () {
                Route::get('/', [HCMController::class, 'index']);
            });
        // ga routes
        Route::prefix('ga')
            ->name('.ga')
            ->group(function () {
                Route::get('/', [GAController::class, 'index']);
            });
        // qrd routes
        Route::prefix('qrd')
            ->name('.qrd')
            ->group(function () {
                Route::get('/', [QRDController::class, 'index']);
            });
        // finance routes
        Route::prefix('finance')
            ->name('.finance')
            ->group(function () {
                Route::get('/', [FinanceController::class, 'index']);
            });
    });
// school routes
Route::middleware(['auth', 'verified'])
    ->prefix('school')
    ->name('school')
    ->group(function () {
        Route::get('/', [SchoolController::class, 'index']);
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
