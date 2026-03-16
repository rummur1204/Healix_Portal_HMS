<?php


use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketAttachmentController;
use App\Http\Controllers\TicketCommentController;
use App\Http\Controllers\TicketTagController;
use App\Http\Controllers\TicketHistoryController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FeatureRequestDetailController;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/


Route::middleware(['auth'])->group(function () {

    // Feature requests nested under tickets
    Route::get('/tickets/{ticket}/feature-requests', [FeatureRequestDetailController::class,'index'])
        ->name('tickets.feature-requests.index');

    Route::post('/tickets/{ticket}/feature-requests', [FeatureRequestDetailController::class,'store'])
        ->name('tickets.feature-requests.store');

    Route::put('/tickets/{ticket}/feature-requests/{id}', [FeatureRequestDetailController::class,'update'])
        ->name('tickets.feature-requests.update');

    Route::delete('/tickets/{ticket}/feature-requests/{id}', [FeatureRequestDetailController::class,'destroy'])
        ->name('tickets.feature-requests.destroy');

});



    
       Route::get('/tickets/{id}/detail', [TicketController::class, 'detail'])
        ->name('tickets.detail');

    // Tickets
    Route::resource('tickets', TicketController::class);
Route::put('/tickets/{ticket}/toggle-active', [TicketController::class, 'toggleActive'])
    ->name('tickets.toggle-active');
    // Ticket Comments
    Route::get('/tickets/{ticket}/comments', [TicketCommentController::class, 'index'])->name('tickets.comments.index');
    Route::post('/tickets/{ticket}/comments', [TicketCommentController::class, 'store'])->name('tickets.comments.store');
    Route::put('/tickets/{ticket}/comments/{comment}', [TicketCommentController::class, 'update'])->name('tickets.comments.update');
    Route::delete('/tickets/{ticket}/comments/{comment}', [TicketCommentController::class, 'destroy'])->name('tickets.comments.destroy');
Route::get('/tickets/{id}/detail', [TicketController::class, 'detail'])->name('tickets.detail');
    
// Ticket Attachments


Route::middleware(['auth'])->group(function () {
    
    Route::prefix('tickets/{ticket}/attachments')->name('tickets.attachments.')->group(function () {
        Route::get('/', [TicketAttachmentController::class, 'index'])->name('index');
        Route::post('/', [TicketAttachmentController::class, 'store'])->name('store');
        Route::delete('/{attachment}', [TicketAttachmentController::class, 'destroy'])->name('destroy');
        Route::get('/{attachment}/download', [TicketAttachmentController::class, 'download'])->name('download');
    });
});






   
    // Ticket Tags
    Route::get('/tickets/{ticket}/tags', [TicketTagController::class, 'index'])->name('tickets.tags.index');
    Route::post('/tickets/{ticket}/tags', [TicketTagController::class, 'store'])->name('tickets.tags.store');
    Route::delete('/tickets/{ticket}/tags/{tag}', [TicketTagController::class, 'destroy'])->name('tickets.tags.destroy');

   
    Route::get('/ticket/history/view', [TicketHistoryController::class, 'indexAll'])
    ->name('tickets.history.view');

Route::get('/tickets/history', [TicketHistoryController::class, 'indexAll'])
    ->name('tickets.history.all');

Route::delete('/history/{id}', [TicketHistoryController::class, 'destroy'])
    ->name('history.destroy');


   

require __DIR__.'/auth.php';