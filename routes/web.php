<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketAttachmentController;
use App\Http\Controllers\TicketCommentController;
use App\Http\Controllers\TicketTagController;
use App\Http\Controllers\TicketHistoryController;
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
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

// Redirect root to login
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

    Route::post('clients/{client}/change-status', [ClientController::class, 'changeStatus'])->name('clients.change-status');

    Route::post('/clients/{client}/technical-info', [ClientTechnicalInfoController::class, 'storeOrUpdate'])->name('clients.technical-info.save');

    Route::post('/clients/{client}/notes', [ClientNoteController::class, 'store'])->name('clients.notes.store');
    Route::put('/clients/{client}/notes/{note}', [ClientNoteController::class, 'update'])->name('clients.notes.update');
    Route::delete('/clients/{client}/notes/{note}', [ClientNoteController::class, 'destroy'])->name('clients.notes.destroy');

    Route::post('/clients/{client}/docs', [ClientDocController::class, 'store'])->name('clients.docs.store');
    Route::delete('/docs/{doc}', [ClientDocController::class, 'destroy'])->name('clients.docs.destroy');

    Route::post('/clients/{client}/tasks', [ClientTaskController::class, 'store'])->name('clients.tasks.store');
    Route::patch('/tasks/{task}', [ClientTaskController::class, 'update'])->name('clients.tasks.update');
    Route::delete('/tasks/{task}', [ClientTaskController::class, 'destroy'])->name('clients.tasks.destroy');

});

/*
|--------------------------------------------------------------------------
| SUBSCRIPTIONS MODULE
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('subscriptions')->name('subscriptions.')->group(function () {

    // Main subscription page
    Route::get('/', function () {
        try {
            $planController = app(SubscriptionPlanController::class);
            $subscriptionController = app(ClientSubscriptionController::class);
            $renewalController = app(RenewalController::class);

            $plans = $planController->getPlansForIndex() ?? [];
            $clientSubscriptions = $subscriptionController->getAllSubscriptionsForIndex() ?? [];
            $renewals = $renewalController->getRenewalsForIndex(30) ?? [];
            $clients = Client::select('id', 'organization_name', 'primary_contact_email', 'primary_contact_phone')
                ->orderBy('organization_name')->get();

            $pendingApprovalsCount = class_exists('App\Models\DiscountApproval')
                ? \App\Models\DiscountApproval::where('status', 'pending')->count()
                : 0;

            return Inertia\Inertia::render('Subscriptions/Index', [
                'plans' => $plans,
                'clientSubscriptions' => $clientSubscriptions,
                'renewals' => $renewals,
                'clients' => $clients,
                'filters' => request()->all(),
                'pendingApprovalsCount' => $pendingApprovalsCount,
                'error' => null
            ]);

        } catch (\Exception $e) {
            Log::error('Error in subscriptions route: ' . $e->getMessage());
            return Inertia\Inertia::render('Subscriptions/Index', [
                'plans' => [],
                'clientSubscriptions' => [],
                'renewals' => [],
                'clients' => [],
                'filters' => request()->all(),
                'pendingApprovalsCount' => 0,
                'error' => 'Failed to load subscription data: ' . $e->getMessage()
            ]);
        }
    })->name('index');

    // Subscription Plans
    Route::resource('plans', SubscriptionPlanController::class)
        ->parameters(['plans' => 'subscriptionPlan']);
    Route::post('plans/{subscriptionPlan}/toggle-status', [SubscriptionPlanController::class, 'toggleStatus'])->name('plans.toggle-status');
    Route::get('plans/active/list', [SubscriptionPlanController::class, 'activePlans'])->name('plans.active');

    // Client Subscriptions
    Route::get('client-subscriptions/all', [ClientSubscriptionController::class, 'allSubscriptions'])->name('client-subscriptions.all');
    Route::get('subscription/{id}', [ClientSubscriptionController::class, 'show'])->name('subscription.show');
    Route::post('assign', [ClientSubscriptionController::class, 'assign'])->name('assign');
    Route::put('subscription/{id}', [ClientSubscriptionController::class, 'update'])->name('subscription.update');
    Route::delete('subscription/{id}', [ClientSubscriptionController::class, 'destroy'])->name('subscription.destroy');

    Route::put('subscription/{id}/cancel', [ClientSubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::put('subscription/{id}/suspend', [ClientSubscriptionController::class, 'suspend'])->name('subscription.suspend');
    Route::put('subscription/{id}/reactivate', [ClientSubscriptionController::class, 'reactivate'])->name('subscription.reactivate');
    Route::put('subscription/{id}/activate', [ClientSubscriptionController::class, 'activate'])->name('subscription.activate');
    Route::post('subscription/{id}/change-plan', [ClientSubscriptionController::class, 'changePlan'])->name('subscription.change-plan');
    Route::post('subscription/{id}/apply-discount', [ClientSubscriptionController::class, 'applyDiscount'])->name('subscription.apply-discount');
    Route::get('subscription/{id}/history', [ClientSubscriptionController::class, 'history'])->name('subscription.history');

    // Renewals
    Route::get('renewals', [RenewalController::class, 'index'])->name('renewals.index');
    Route::get('renewals/due/{days?}', [RenewalController::class, 'due'])->name('renewals.due');
    Route::post('renewals/{id}/process', [RenewalController::class, 'process'])->name('renewals.process');

    // Discount Approvals
    Route::get('discount-approvals/pending', [DiscountApprovalController::class, 'pending'])->name('discount-approvals.pending');
    Route::post('discount-approvals/{id}/approve', [DiscountApprovalController::class, 'approve'])->name('discount-approvals.approve');
    Route::post('discount-approvals/{id}/reject', [DiscountApprovalController::class, 'reject'])->name('discount-approvals.reject');

    // Payments
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::post('record', [PaymentController::class, 'record'])->name('record');
        Route::get('subscription/{subscriptionId}', [PaymentController::class, 'history'])->name('history');
        Route::post('{id}/void', [PaymentController::class, 'void'])->name('void');
    });

    // Reports
    Route::get('reports/renewals', [SubscriptionReportController::class, 'renewals'])->name('reports.renewals');
    Route::get('reports/summary', [SubscriptionReportController::class, 'summary'])->name('reports.summary');

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

    Route::prefix('payments')->name('payments.')->group(function () {
        Route::post('record', [PaymentController::class, 'record'])->name('record');
        Route::get('subscription/{subscriptionId}', [PaymentController::class, 'history'])->name('history');
        Route::post('{id}/void', [PaymentController::class, 'void'])->name('void');
    });

    // Test route
    Route::get('/test/payments', function () {
        return Inertia\Inertia::render('Test/Payments');
    })->name('test.payments');

    // Reports
    Route::get('reports/renewals', [SubscriptionReportController::class, 'renewals'])->name('reports.renewals');
    Route::get('reports/summary', [SubscriptionReportController::class, 'summary'])->name('reports.summary');
});

/*
|--------------------------------------------------------------------------
| CLIENTS API ROUTES
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


|--------------------------------------------------------------------------
| DASHBOARD + TICKETS
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard & Profile
    Route::get('/dashboard', function () {
        return Inertia\Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tickets
    Route::resource('tickets', TicketController::class);
    Route::put('/tickets/{ticket}/toggle-active', [TicketController::class, 'toggleActive'])->name('tickets.toggle-active');

    // Ticket Comments
    Route::get('/tickets/{ticket}/comments', [TicketCommentController::class, 'index'])->name('tickets.comments.index');
    Route::post('/tickets/{ticket}/comments', [TicketCommentController::class, 'store'])->name('tickets.comments.store');
    Route::put('/tickets/{ticket}/comments/{comment}', [TicketCommentController::class, 'update'])->name('tickets.comments.update');
    Route::delete('/tickets/{ticket}/comments/{comment}', [TicketCommentController::class, 'destroy'])->name('tickets.comments.destroy');

    // Ticket Attachments
    Route::prefix('tickets/{ticket}/attachments')->name('tickets.attachments.')->group(function () {
        Route::get('/', [TicketAttachmentController::class, 'index'])->name('index');
        Route::post('/', [TicketAttachmentController::class, 'store'])->name('store');
        Route::delete('/{attachment}', [TicketAttachmentController::class, 'destroy'])->name('destroy');
        Route::get('/{attachment}/download', [TicketAttachmentController::class, 'download'])->name('download');
    });

    // Ticket Tags
    Route::get('/tickets/{ticket}/tags', [TicketTagController::class, 'index'])->name('tickets.tags.index');
    Route::post('/tickets/{ticket}/tags', [TicketTagController::class, 'store'])->name('tickets.tags.store');
    Route::delete('/tickets/{ticket}/tags/{tag}', [TicketTagController::class, 'destroy'])->name('tickets.tags.destroy');

    // Ticket History
    Route::get('/ticket/history/view', [TicketHistoryController::class, 'indexAll'])->name('tickets.history.view');
    Route::get('/tickets/history', [TicketHistoryController::class, 'indexAll'])->name('tickets.history.all');
    Route::delete('/history/{id}', [TicketHistoryController::class, 'destroy'])->name('history.destroy');

});

// Misc API routes
Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {
    Route::get('/users/all', [SettingsController::class, 'getUsers'])->name('users.all');
    Route::get('/roles/all', [SettingsController::class, 'getRoles'])->name('roles.all');
    Route::get('/permissions/all', [SettingsController::class, 'getPermissions'])->name('permissions.all');
    Route::get('/organization-types/all', [SettingsController::class, 'getOrganizationTypes'])->name('org-types.all');
});

require __DIR__.'/auth.php';