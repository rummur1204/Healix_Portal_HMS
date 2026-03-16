<?php


use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SlaConfigurationController;
use App\Http\Controllers\SlaTrackingController;
use App\Http\Controllers\ProductVersionController;
use App\Http\Controllers\ClientDeploymentController;
use App\Http\Controllers\ClientCurrentVersionController;

Route::resource('client-current-versions', ClientCurrentVersionController::class);

Route::resource('client-deployments', ClientDeploymentController::class);

Route::prefix('product-versions')->name('product-versions.')->group(function () {
    Route::get('/', [ProductVersionController::class, 'index'])->name('index');
    Route::get('/create', [ProductVersionController::class, 'create'])->name('create');
    Route::post('/', [ProductVersionController::class, 'store'])->name('store');
    Route::get('/{productVersion}/edit', [ProductVersionController::class, 'edit'])->name('edit');
    Route::put('/{productVersion}', [ProductVersionController::class, 'update'])->name('update');
    Route::delete('/{productVersion}', [ProductVersionController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::prefix('sla-tracking')->name('sla-tracking.')->group(function () {
        Route::get('/', [SlaTrackingController::class, 'index'])->name('index');
        Route::get('/create', [SlaTrackingController::class, 'create'])->name('create');
        Route::post('/', [SlaTrackingController::class, 'store'])->name('store');
        Route::get('/{slaTracking}/edit', [SlaTrackingController::class, 'edit'])->name('edit');
        Route::put('/{slaTracking}', [SlaTrackingController::class, 'update'])->name('update');
        Route::delete('/{slaTracking}', [SlaTrackingController::class, 'destroy'])->name('destroy');
    });

});

Route::middleware(['auth'])->group(function () {

Route::get('/sla-configurations', [SlaConfigurationController::class,'index'])
->name('sla-configurations.index');

Route::get('/sla-configurations/create', [SlaConfigurationController::class,'create'])
->name('sla-configurations.create');

Route::post('/sla-configurations', [SlaConfigurationController::class,'store'])
->name('sla-configurations.store');

Route::get('/sla-configurations/{id}/edit', [SlaConfigurationController::class,'edit'])
->name('sla-configurations.edit');

Route::put('/sla-configurations/{id}', [SlaConfigurationController::class,'update'])
->name('sla-configurations.update');

Route::delete('/sla-configurations/{id}', [SlaConfigurationController::class,'destroy'])
->name('sla-configurations.destroy');

});