<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Models\Client;
use App\Http\Controllers\Clients\ClientController;
use App\Http\Controllers\Clients\ClientTechnicalInfoController;
use App\Http\Controllers\Clients\ClientNoteController;
use App\Http\Controllers\Clients\ClientDocController;
use App\Http\Controllers\Clients\ClientTaskController;



// ============ CLIENTS MODULE ROUTES (Protected) ============

Route::middleware(['auth'])->group(function () {

    Route::resource('clients', ClientController::class);

    Route::post('clients/{client}/change-status',
        [ClientController::class, 'changeStatus']
    )->name('clients.change-status');

    Route::post('/clients/{client}/technical-info',
    [ClientTechnicalInfoController::class, 'storeOrUpdate']
    )->name('clients.technical-info.save');

    Route::post('/clients/{client}/notes', [ClientNoteController::class, 'store'])
    ->name('clients.notes.store');

    Route::put('/clients/{client}/notes/{note}', [ClientNoteController::class, 'update'])
    ->name('clients.notes.update');

    Route::delete('/clients/{client}/notes/{note}', [ClientNoteController::class, 'destroy'])
    ->name('clients.notes.destroy');

    Route::post(
    '/clients/{client}/docs',
    [ClientDocController::class, 'store']
    )->name('clients.docs.store');

    Route::delete(
    '/docs/{doc}',
    [ClientDocController::class, 'destroy']
    )->name('clients.docs.destroy');

    Route::post('/clients/{client}/tasks', [ClientTaskController::class, 'store'])
    ->name('clients.tasks.store');

Route::patch('/tasks/{task}', [ClientTaskController::class, 'update'])
    ->name('clients.tasks.update');

Route::delete('/tasks/{task}', [ClientTaskController::class, 'destroy'])
    ->name('clients.tasks.destroy');

});