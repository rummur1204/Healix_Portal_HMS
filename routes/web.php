<?php

use App\Http\Controllers\ProfileController;



use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

// Redirect root to login
Route::get('/', function () {
    return redirect('/login');
});




Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return Inertia\Inertia::render('Dashboard');
    })->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    
});




require __DIR__.'/auth.php';
require __DIR__.'/subscription.php';
require __DIR__.'/settings.php';
require __DIR__.'/clients.php';
