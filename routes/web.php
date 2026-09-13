<?php

use App\Http\Controllers\DateConfirmationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/date-confirmations', [DateConfirmationController::class, 'store'])
    ->name('date-confirmations.store');
