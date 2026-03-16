<?php

use App\Http\Controllers\Settings\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SETTINGS MODULE
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('settings')->name('settings.')->group(function () {
    // Main settings page
    Route::get('/', [SettingsController::class, 'index'])->name('index');
    
    // User management routes
    Route::get('/users', [SettingsController::class, 'getUsers'])->name('users');
    Route::post('/users', [SettingsController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{id}', [SettingsController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}', [SettingsController::class, 'destroyUser'])->name('users.destroy');
    
    // Role management routes
    Route::get('/roles', [SettingsController::class, 'getRoles'])->name('roles');
    Route::post('/roles', [SettingsController::class, 'storeRole'])->name('roles.store');
    Route::put('/roles/{id}', [SettingsController::class, 'updateRole'])->name('roles.update');
    Route::delete('/roles/{id}', [SettingsController::class, 'destroyRole'])->name('roles.destroy');
    
    // Permission routes
    Route::get('/permissions', [SettingsController::class, 'getPermissions'])->name('permissions');
    
    // Organization type routes
    Route::get('/organization-types', [SettingsController::class, 'getOrganizationTypes'])->name('org-types');
    Route::post('/organization-types', [SettingsController::class, 'storeOrganizationType'])->name('org-types.store');
    Route::put('/organization-types/{id}', [SettingsController::class, 'updateOrganizationType'])->name('org-types.update');
    Route::delete('/organization-types/{id}', [SettingsController::class, 'destroyOrganizationType'])->name('org-types.destroy');
    
    // Stats and refresh
    Route::get('/stats', [SettingsController::class, 'getStats'])->name('stats');
    Route::get('/refresh', [SettingsController::class, 'refresh'])->name('refresh');
});