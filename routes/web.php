<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketAttachmentController;
use App\Http\Controllers\TicketCommentController;
use App\Http\Controllers\TicketTagController;
use App\Http\Controllers\TicketHistoryController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Subscription\SubscriptionPlanController;
use App\Http\Controllers\Subscription\ClientSubscriptionController;
use App\Http\Controllers\Subscription\RenewalController;
use App\Http\Controllers\Subscription\PaymentController;
use App\Http\Controllers\Subscription\SubscriptionReportController;
use App\Http\Controllers\Settings\SettingsController;
use App\Http\Controllers\Settings\UserController;
use App\Http\Controllers\Settings\RoleController;
use App\Http\Controllers\Settings\OrganizationTypeController;
use App\Models\Client;
use App\Http\Controllers\FeatureRequestDetailController;
use App\Http\Controllers\SlaConfigurationController;
use App\Http\Controllers\SlaTrackingController;

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

Route::middleware(['auth'])->group(function () {

    Route::get('/feature-requests', [FeatureRequestDetailController::class,'index'])
        ->name('feature-requests.index');

    Route::get('/feature-requests/create', [FeatureRequestDetailController::class,'create'])
        ->name('feature-requests.create');

    Route::post('/feature-requests', [FeatureRequestDetailController::class,'store'])
        ->name('feature-requests.store');

    Route::get('/feature-requests/{id}/edit', [FeatureRequestDetailController::class,'edit'])
        ->name('feature-requests.edit');

    Route::put('/feature-requests/{id}', [FeatureRequestDetailController::class,'update'])
        ->name('feature-requests.update');

    Route::delete('/feature-requests/{id}', [FeatureRequestDetailController::class,'destroy'])
        ->name('feature-requests.destroy');

});
/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect('/login');
});



    






/*
|--------------------------------------------------------------------------
| Authenticated Routes (Tickets, Dashboard, Profile)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {
        return Inertia\Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
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


// Inside your auth middleware group
Route::middleware(['auth'])->group(function () {
    // Your existing routes...
    
    // Ticket attachments routes
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

    // Ticket History
    // ... existing code up to line 193 ...

    // Ticket History
    // REMOVE THIS LINE: <?php

    // Your existing routes...

    // Ticket History Routes
    Route::get('/ticket/history/view', [TicketHistoryController::class, 'indexAll'])
    ->name('tickets.history.view');

Route::get('/tickets/history', [TicketHistoryController::class, 'indexAll'])
    ->name('tickets.history.all');

// Delete history entry
Route::delete('/history/{id}', [TicketHistoryController::class, 'destroy'])
    ->name('history.destroy');

    });
   

require __DIR__.'/auth.php';