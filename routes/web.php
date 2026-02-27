<?php

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
use App\Http\Controllers\Clients\ClientController;
use App\Models\Client;

// ============ AUTHENTICATION ROUTES (Added by Breeze) ============
Route::get('/', function () {
    return redirect('/login');
});

// ============ CLIENTS MODULE ROUTES (Protected) ============

Route::middleware(['auth'])->group(function () {

    Route::resource('clients', ClientController::class);

    Route::post('clients/{client}/change-status',
        [ClientController::class, 'changeStatus']
    )->name('clients.change-status');

});
// ============ SUBSCRIPTION MODULE ROUTES (Protected) ============
Route::middleware(['auth'])->prefix('subscriptions')->name('subscriptions.')->group(function () {
    
    // ===== MAIN SUBSCRIPTION PAGE WITH TABS =====
    Route::get('/', function () {
        return Inertia\Inertia::render('Subscriptions/Index');
    })->name('index');
    
    // ===== SUBSCRIPTION PLANS =====
    Route::resource('plans', SubscriptionPlanController::class)
        ->parameters(['plans' => 'subscriptionPlan']);
    Route::post('plans/{id}/toggle-status', [SubscriptionPlanController::class, 'toggleStatus'])
        ->name('plans.toggle-status');
    Route::get('plans/active/list', [SubscriptionPlanController::class, 'activePlans'])
        ->name('plans.active');

    // ===== CLIENT SUBSCRIPTIONS =====
    // Get all subscriptions for a client
    Route::get('client/{clientId}', [ClientSubscriptionController::class, 'clientSubscriptions'])
        ->name('client.subscriptions');
    
    // Get all client subscriptions for the tab
    Route::get('client-subscriptions/all', [ClientSubscriptionController::class, 'allSubscriptions'])
        ->name('client-subscriptions.all');
    
    // Assign subscription to client
    Route::post('assign', [ClientSubscriptionController::class, 'assign'])
        ->name('assign');
    
    // Single subscription operations
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

    // ===== RENEWALS =====
    Route::get('renewals', [RenewalController::class, 'index'])->name('renewals.index');
    Route::get('renewals/due/{days?}', [RenewalController::class, 'due'])->name('renewals.due');
    Route::post('renewals/{id}/process', [RenewalController::class, 'process'])->name('renewals.process');

    // ===== PAYMENTS (UPDATED WITH ALL ROUTES) =====
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::post('record', [PaymentController::class, 'record'])->name('record');
        Route::get('subscription/{subscriptionId}', [PaymentController::class, 'history'])->name('history');
        Route::post('{id}/void', [PaymentController::class, 'void'])->name('void');
        Route::get('{subscriptionId}/summary', [PaymentController::class, 'summary'])->name('summary');
    });

    // ===== REPORTS =====
    Route::get('reports/renewals', [SubscriptionReportController::class, 'renewals'])->name('reports.renewals');
    Route::get('reports/summary', [SubscriptionReportController::class, 'summary'])->name('reports.summary');
    Route::get('reports/mrr', [SubscriptionReportController::class, 'mrr'])->name('reports.mrr');
    Route::get('reports/churn', [SubscriptionReportController::class, 'churn'])->name('reports.churn');
    Route::get('reports/export/renewals', [SubscriptionReportController::class, 'exportRenewals'])->name('reports.export.renewals');
});

// ============ CLIENTS API ROUTES (FIXED) ============
Route::middleware(['auth'])->prefix('clients')->name('clients.')->group(function () {
    Route::get('/list', function () {
        try {
            $clients = Client::select('id', 'organization_name', 'primary_contact_email as email')
                ->orderBy('organization_name')
                ->get();
            return response()->json($clients);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->name('list');
    
    Route::get('/active', function () {
        try {
            $clients = Client::where('status', 'active')
                ->select('id', 'organization_name', 'primary_contact_email as email')
                ->orderBy('organization_name')
                ->get();
            return response()->json($clients);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->name('active');
    
    Route::get('/with-subscriptions', function () {
        try {
            $clients = Client::whereHas('subscriptions')
                ->withCount('subscriptions')
                ->select('id', 'organization_name', 'primary_contact_email as email')
                ->orderBy('organization_name')
                ->get();
            return response()->json($clients);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->name('with-subscriptions');
});

// ============ SETTINGS MODULE ROUTES ============
Route::middleware(['auth'])->prefix('settings')->name('settings.')->group(function () {
    
    // Main Settings Page
    Route::get('/', [SettingsController::class, 'index'])->name('index');
    
    // Settings API Routes
    Route::get('/stats', [SettingsController::class, 'getStats'])->name('stats');
    Route::get('/refresh', [SettingsController::class, 'refresh'])->name('refresh');
    Route::get('/users/all', [SettingsController::class, 'getUsers'])->name('users.all');
    Route::get('/roles/all', [SettingsController::class, 'getRoles'])->name('roles.all');
    Route::get('/permissions/all', [SettingsController::class, 'getPermissions'])->name('permissions.all');
    Route::get('/organization-types/all', [SettingsController::class, 'getOrganizationTypes'])->name('org-types.all');
    
    // ===== USER MANAGEMENT API ROUTES =====
    Route::prefix('api')->name('api.')->group(function () {
        // Users
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/list', [UserController::class, 'list'])->name('users.list');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        
        // Roles
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/list', [RoleController::class, 'list'])->name('roles.list');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{id}', [RoleController::class, 'show'])->name('roles.show');
        Route::put('/roles/{id}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
        
        // Organization Types
        Route::get('/organization-types', [OrganizationTypeController::class, 'index'])->name('org-types.index');
        Route::get('/organization-types/list', [OrganizationTypeController::class, 'list'])->name('org-types.list');
        Route::post('/organization-types', [OrganizationTypeController::class, 'store'])->name('org-types.store');
        Route::get('/organization-types/{id}', [OrganizationTypeController::class, 'show'])->name('org-types.show');
        Route::put('/organization-types/{id}', [OrganizationTypeController::class, 'update'])->name('org-types.update');
        Route::delete('/organization-types/{id}', [OrganizationTypeController::class, 'destroy'])->name('org-types.destroy');
        Route::post('/organization-types/{id}/toggle-status', [OrganizationTypeController::class, 'toggleStatus'])->name('org-types.toggle-status');
    });
});

// ============ DASHBOARD ROUTE ============
Route::middleware(['auth'])->get('/dashboard', function () {
    return Inertia\Inertia::render('Dashboard');
})->name('dashboard');

// ============ HEALTH CHECK ROUTE ============
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'environment' => app()->environment()
    ]);
})->name('health');

// ============ TEST ROUTE FOR PAYMENTS (Remove in production) ============
Route::middleware(['auth'])->get('/test/payments', function () {
    return Inertia\Inertia::render('Test/Payments');
})->name('test.payments');

// ============ INCLUDE AUTH ROUTES (If Breeze is installed) ============
require __DIR__.'/auth.php';