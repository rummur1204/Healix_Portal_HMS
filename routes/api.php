<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ClientController;

// Route::apiResource('clients', ClientController::class);
Route::post('/clients', [ClientController::class, 'store']);
Route::get('/clients', [ClientController::class, 'index']);