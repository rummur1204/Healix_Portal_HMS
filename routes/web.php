<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketAttachmentController;
use App\Http\Controllers\TicketCommentController;
use App\Http\Controllers\TicketTagController;
use App\Http\Controllers\TicketHistoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Subscription\SubscriptionPlanController;
use App\Http\Controllers\Subscription\ClientSubscriptionController;
use App\Http\Controllers\Subscription\RenewalController;
use App\Http\Controllers\Subscription\PaymentController;
use App\Http\Controllers\Subscription\SubscriptionReportController;
use App\Http\Controllers\Subscription\DiscountApprovalController;
use App\Http\Controllers\Settings\SettingsController;
use App\Http\Controllers\Clients\ClientController;
use App\Http\Controllers\Clients\ClientTechnicalInfoController;
use App\Http\Controllers\Clients\ClientNoteController;
use App\Http\Controllers\Clients\ClientDocController;
use App\Http\Controllers\Clients\ClientTaskController;
use App\Models\Client;
use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    return redirect('/login');
});


/*
|--------------------------------------------------------------------------
| CLIENTS MODULE
|--------------------------------------------------------------------------
*/

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

    Route::post('/clients/{client}/docs',
        [ClientDocController::class, 'store']
    )->name('clients.docs.store');

    Route::delete('/docs/{doc}',
        [ClientDocController::class, 'destroy']
    )->name('clients.docs.destroy');

    Route::post('/clients/{client}/tasks', [ClientTaskController::class, 'store'])
        ->name('clients.tasks.store');

    Route::patch('/tasks/{task}', [ClientTaskController::class, 'update'])
        ->name('clients.tasks.update');

    Route::delete('/tasks/{task}', [ClientTaskController::class, 'destroy'])
        ->name('clients.tasks.destroy');
});


/*
|--------------------------------------------------------------------------
| SUBSCRIPTIONS MODULE
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->get('/subscriptions', function () {

    try {

        $planController = app(SubscriptionPlanController::class);
        $subscriptionController = app(ClientSubscriptionController::class);
        $renewalController = app(RenewalController::class);

        $plans = $planController->getPlansForIndex() ?? [];
        $clientSubscriptions = $subscriptionController->getAllSubscriptionsForIndex() ?? [];
        $renewals = $renewalController->getRenewalsForIndex(30) ?? [];

        $clients = Client::select(
            'id',
            'organization_name',
            'primary_contact_email',
            'primary_contact_phone'
        )->orderBy('organization_name')->get();

        $pendingApprovalsCount = 0;

        if (class_exists('App\Models\DiscountApproval')) {
            $pendingApprovalsCount = \App\Models\DiscountApproval::where('status', 'pending')->count();
        }

        return Inertia\Inertia::render('Subscriptions/Index', [
            'plans' => $plans,
            'clientSubscriptions' => $clientSubscriptions,
            'renewals' => $renewals,
            'clients' => $clients,
            'filters' => request()->all(),
            'pendingApprovalsCount' => $pendingApprovalsCount
        ]);

    } catch (\Exception $e) {

        Log::error('Subscription page error: '.$e->getMessage());

        return Inertia\Inertia::render('Subscriptions/Index', [
            'plans' => [],
            'clientSubscriptions' => [],
            'renewals' => [],
            'clients' => [],
            'filters' => request()->all(),
            'pendingApprovalsCount' => 0
        ]);
    }

})->name('subscriptions.index');


Route::middleware(['auth'])->prefix('subscriptions')->name('subscriptions.')->group(function () {

    // Plans
    Route::resource('plans', SubscriptionPlanController::class)
        ->parameters(['plans' => 'subscriptionPlan']);

    Route::post('plans/{id}/toggle-status',
        [SubscriptionPlanController::class, 'toggleStatus']
    )->name('plans.toggle-status');

    Route::get('plans/active/list',
        [SubscriptionPlanController::class, 'activePlans']
    )->name('plans.active');

    // Client Subscriptions
    Route::get('client-subscriptions/all',
        [ClientSubscriptionController::class, 'allSubscriptions']
    )->name('client-subscriptions.all');

    Route::post('assign',
        [ClientSubscriptionController::class, 'assign']
    )->name('assign');

    Route::get('subscription/{id}',
        [ClientSubscriptionController::class, 'show']
    )->name('subscription.show');

    Route::put('subscription/{id}/cancel',
        [ClientSubscriptionController::class, 'cancel']
    )->name('subscription.cancel');

    Route::put('subscription/{id}/suspend',
        [ClientSubscriptionController::class, 'suspend']
    )->name('subscription.suspend');

    Route::put('subscription/{id}/reactivate',
        [ClientSubscriptionController::class, 'reactivate']
    )->name('subscription.reactivate');

    Route::get('subscription/{id}/history',
        [ClientSubscriptionController::class, 'history']
    )->name('subscription.history');

    // Renewals
    Route::get('renewals', [RenewalController::class, 'index'])->name('renewals.index');

    Route::get('renewals/due/{days?}',
        [RenewalController::class, 'due']
    )->name('renewals.due');

    Route::post('renewals/{id}/process',
        [RenewalController::class, 'process']
    )->name('renewals.process');

    // Payments
    Route::prefix('payments')->name('payments.')->group(function () {

        Route::post('record',
            [PaymentController::class, 'record']
        )->name('record');

        Route::get('subscription/{subscriptionId}',
            [PaymentController::class, 'history']
        )->name('history');

        Route::post('{id}/void',
            [PaymentController::class, 'void']
        )->name('void');
    });

    // Reports
    Route::get('reports/renewals',
        [SubscriptionReportController::class, 'renewals']
    )->name('reports.renewals');

    Route::get('reports/summary',
        [SubscriptionReportController::class, 'summary']
    )->name('reports.summary');

});


/*
|--------------------------------------------------------------------------
| SETTINGS MODULE
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('settings')->name('settings.')->group(function () {

    Route::get('/', [SettingsController::class, 'index'])->name('index');

    Route::get('/stats', [SettingsController::class, 'getStats'])->name('stats');

    Route::get('/refresh', [SettingsController::class, 'refresh'])->name('refresh');

});


/*
|--------------------------------------------------------------------------
| DASHBOARD + TICKETS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {
        return Inertia\Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('tickets', TicketController::class);

    Route::put('/tickets/{ticket}/toggle-active',
        [TicketController::class, 'toggleActive']
    )->name('tickets.toggle-active');

    Route::get('/tickets/{ticket}/comments',
        [TicketCommentController::class, 'index']
    )->name('tickets.comments.index');

    Route::post('/tickets/{ticket}/comments',
        [TicketCommentController::class, 'store']
    )->name('tickets.comments.store');

});


require __DIR__.'/auth.php';