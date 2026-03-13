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
| Subscription Module Routes (Protected)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('subscriptions')->name('subscriptions.')->group(function () {

    Route::get('/', function () {
        return Inertia\Inertia::render('Subscriptions/Index');
    })->name('index');

    Route::resource('plans', SubscriptionPlanController::class)
        ->parameters(['plans' => 'subscriptionPlan']);
    Route::post('plans/{id}/toggle-status', [SubscriptionPlanController::class, 'toggleStatus'])
        ->name('plans.toggle-status');
    Route::get('plans/active/list', [SubscriptionPlanController::class, 'activePlans'])
        ->name('plans.active');

    Route::get('client/{clientId}', [ClientSubscriptionController::class, 'clientSubscriptions'])
        ->name('client.subscriptions');
    Route::get('client-subscriptions/all', [ClientSubscriptionController::class, 'allSubscriptions'])
        ->name('client-subscriptions.all');
    Route::post('assign', [ClientSubscriptionController::class, 'assign'])
        ->name('assign');
    Route::get('subscription/{id}', [ClientSubscriptionController::class, 'show'])
        ->name('subscription.show');
    Route::put('subscription/{id}/cancel', [ClientSubscriptionController::class, 'cancel'])
        ->name('subscription.cancel');
    Route::put('subscription/{id}/suspend', [ClientSubscriptionController::class, 'suspend'])
        ->name('subscription.suspend');
    Route::put('subscription/{id}/reactivate', [ClientSubscriptionController::class, 'reactivate'])
        ->name('subscription.reactivate');
    Route::get('subscription/{id}/history', [ClientSubscriptionController::class, 'history'])
        ->name('subscription.history');

    Route::get('renewals', [RenewalController::class, 'index'])->name('renewals.index');
    Route::get('renewals/due/{days?}', [RenewalController::class, 'due'])->name('renewals.due');
    Route::post('renewals/{id}/process', [RenewalController::class, 'process'])->name('renewals.process');

    Route::prefix('payments')->name('payments.')->group(function () {
        Route::post('record', [PaymentController::class, 'record'])->name('record');
        Route::get('subscription/{subscriptionId}', [PaymentController::class, 'history'])->name('history');
        Route::post('{id}/void', [PaymentController::class, 'void'])->name('void');
        Route::get('{subscriptionId}/summary', [PaymentController::class, 'summary'])->name('summary');
    });

    // Test route (keep from HEAD branch)
    Route::get('/test/payments', function () {
        return Inertia\Inertia::render('Test/Payments');
    })->name('test.payments');

    // Reports
    Route::get('reports/renewals', [SubscriptionReportController::class, 'renewals'])->name('reports.renewals');
    Route::get('reports/summary', [SubscriptionReportController::class, 'summary'])->name('reports.summary');
    Route::get('reports/mrr', [SubscriptionReportController::class, 'mrr'])->name('reports.mrr');
    Route::get('reports/churn', [SubscriptionReportController::class, 'churn'])->name('reports.churn');
    Route::get('reports/export/renewals', [SubscriptionReportController::class, 'exportRenewals'])->name('reports.export.renewals');
});

/*
|--------------------------------------------------------------------------
| Clients API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('clients')->name('clients.')->group(function () {
    Route::get('/list', function () {
        $clients = Client::select('id', 'organization_name', 'primary_contact_email as email')
            ->orderBy('organization_name')
            ->get();
        return response()->json($clients);
    })->name('list');

    Route::get('/active', function () {
        $clients = Client::where('status', 'active')
            ->select('id', 'organization_name', 'primary_contact_email as email')
            ->orderBy('organization_name')
            ->get();
        return response()->json($clients);
    })->name('active');

    Route::get('/with-subscriptions', function () {
        $clients = Client::whereHas('subscriptions')
            ->withCount('subscriptions')
            ->select('id', 'organization_name', 'primary_contact_email as email')
            ->orderBy('organization_name')
            ->get();
        return response()->json($clients);
    })->name('with-subscriptions');
});

/*
|--------------------------------------------------------------------------
| Settings Module Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [SettingsController::class, 'index'])->name('index');
    Route::get('/stats', [SettingsController::class, 'getStats'])->name('stats');
    Route::get('/refresh', [SettingsController::class, 'refresh'])->name('refresh');
    Route::get('/users/all', [SettingsController::class, 'getUsers'])->name('users.all');
    Route::get('/roles/all', [SettingsController::class, 'getRoles'])->name('roles.all');
    Route::get('/permissions/all', [SettingsController::class, 'getPermissions'])->name('permissions.all');
    Route::get('/organization-types/all', [SettingsController::class, 'getOrganizationTypes'])->name('org-types.all');

    Route::prefix('api')->name('api.')->group(function () {
        // Users, Roles, Organization Types API routes...
    });
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