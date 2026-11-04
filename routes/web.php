<?php

use App\Http\Controllers\WebDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebDashboardController::class, 'index'])->name('dashboard');
Route::post('/pda-request', [WebDashboardController::class, 'storePda'])->name('pda.store');
