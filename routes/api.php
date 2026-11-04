<?php

use App\Http\Controllers\Api\CatalogPdaApiController;
use Illuminate\Support\Facades\Route;

Route::get('/resources', [CatalogPdaApiController::class, 'index']);
Route::post('/pda-request', [CatalogPdaApiController::class, 'store']);
