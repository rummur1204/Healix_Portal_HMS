<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketAttachmentController;
use App\Http\Controllers\TicketCommentController;
use App\Http\Controllers\TicketTagController;
use App\Http\Controllers\TicketHistoryController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
|--------------------------------------------------------------------------
| Ticket Attachments - Standalone Routes
|--------------------------------------------------------------------------
*/
Route::prefix('ticket-attachments')->name('ticket-attachments.')->group(function () {
    Route::get('/', [TicketAttachmentController::class, 'index'])->name('index');
    Route::get('/create', [TicketAttachmentController::class, 'create'])->name('create');
    Route::post('/', [TicketAttachmentController::class, 'store'])->name('store');
    Route::get('/{ticketAttachment}/edit', [TicketAttachmentController::class, 'edit'])->name('edit');
    Route::put('/{ticketAttachment}', [TicketAttachmentController::class, 'update'])->name('update');
    Route::delete('/{ticketAttachment}', [TicketAttachmentController::class, 'destroy'])->name('destroy');
});
/*
|--------------------------------------------------------------------------
| Ticket Tags - Standalone Routes
|--------------------------------------------------------------------------
*/
Route::prefix('ticket-tags')->name('ticket-tags.')->group(function () {
    Route::get('/', [TicketTagController::class, 'index'])->name('index');
    Route::get('/create', [TicketTagController::class, 'create'])->name('create');
    Route::post('/', [TicketTagController::class, 'store'])->name('store');
    Route::get('/{ticket_tag}/edit', [TicketTagController::class, 'edit'])->name('edit');
    Route::put('/{ticket_tag}', [TicketTagController::class, 'update'])->name('update');
    Route::delete('/{ticket_tag}', [TicketTagController::class, 'destroy'])->name('destroy');
});
    /*
    |--------------------------------------------------------------------------
    | Ticket History
    |--------------------------------------------------------------------------
    */

    // Vue page
    Route::get('/ticket/history/view', function () {
        return Inertia::render('Tickets/History');
    })->name('tickets.history.view');

    // JSON API
    Route::get('/tickets/history', [TicketHistoryController::class, 'indexAll'])
        ->name('tickets.history.all');

    Route::get('/tickets/{ticket}/history', [TicketHistoryController::class, 'index'])
        ->name('tickets.history.index');

    Route::get('/tickets/{ticket}/history/summary', [TicketHistoryController::class, 'summary'])
        ->name('tickets.history.summary');

    Route::get('/history/{id}', [TicketHistoryController::class, 'show'])
        ->name('history.show');

    Route::delete('/history/{id}', [TicketHistoryController::class, 'destroy'])
        ->name('history.destroy');

        

    /*
    |--------------------------------------------------------------------------
    | Ticket CRUD
    |--------------------------------------------------------------------------
    */
    Route::resource('tickets', TicketController::class);

    /*
    |--------------------------------------------------------------------------
    | Ticket Comments
    |--------------------------------------------------------------------------
    */
    Route::get('/tickets/{ticket}/comments', [TicketCommentController::class, 'index'])
        ->name('tickets.comments.index');
    Route::post('/tickets/{ticket}/comments', [TicketCommentController::class, 'store'])
        ->name('tickets.comments.store');
    Route::put('/tickets/{ticket}/comments/{comment}', [TicketCommentController::class, 'update'])
        ->name('tickets.comments.update');
    Route::delete('/tickets/{ticket}/comments/{comment}', [TicketCommentController::class, 'destroy'])
        ->name('tickets.comments.destroy');

    /*
    |--------------------------------------------------------------------------
    | Ticket Attachments
    |--------------------------------------------------------------------------
    */
    Route::get('/tickets/{ticket}/attachments', [TicketAttachmentController::class, 'index'])
        ->name('tickets.attachments.index');
    Route::put('/tickets/{ticket}/attachments', [TicketCommentController::class, 'update'])
        ->name('tickets.attachments.update');
    Route::post('/tickets/{ticket}/attachments', [TicketAttachmentController::class, 'store'])
        ->name('tickets.attachments.store');
    Route::delete('/tickets/{ticket}/attachments/{attachment}', [TicketAttachmentController::class, 'destroy'])
        ->name('tickets.attachments.destroy');

    /*
    |--------------------------------------------------------------------------
    | Ticket Tags
    |--------------------------------------------------------------------------
    */
    Route::get('/tickets/{ticket}/tags', [TicketTagController::class, 'index'])
        ->name('tickets.tags.index');
    Route::post('/tickets/{ticket}/tags', [TicketTagController::class, 'store'])
        ->name('tickets.tags.store');
    Route::delete('/tickets/{ticket}/tags/{tag}', [TicketTagController::class, 'destroy'])
        ->name('tickets.tags.destroy');
});

require __DIR__.'/auth.php';