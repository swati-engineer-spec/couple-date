<?php

use App\Http\Controllers\DateConfirmationController;
use App\Http\Controllers\DateProgressController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/date-confirmations', [DateConfirmationController::class, 'store'])
    ->name('date-confirmations.store');
Route::post('/date-progress', [DateProgressController::class, 'store'])
    ->name('date-progress.store');
