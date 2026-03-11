<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Subscription\SubscriptionPlanController;
use App\Http\Controllers\Subscription\ClientSubscriptionController;
use App\Http\Controllers\Subscription\RenewalController;
use App\Http\Controllers\Subscription\PaymentController;
use App\Http\Controllers\Subscription\SubscriptionReportController;
use App\Http\Controllers\Subscription\DiscountApprovalController;
use App\Http\Controllers\Settings\SettingsController;
use App\Http\Controllers\Settings\UserController;
use App\Http\Controllers\Settings\RoleController;
use App\Http\Controllers\Settings\OrganizationTypeController;
use App\Models\Client;
use Illuminate\Support\Facades\Log;

// ============ AUTHENTICATION ROUTES ============
Route::get('/', function () {
    return redirect('/login');
});

// ============ MAIN SUBSCRIPTION PAGE WITH ALL DATA ============
Route::middleware(['auth'])->get('/subscriptions', function () {
    try {
        // Get data from controllers
        $planController = app(SubscriptionPlanController::class);
        $subscriptionController = app(ClientSubscriptionController::class);
        $renewalController = app(RenewalController::class);
        
        // Fetch all data with error handling
        $plans = [];
        $clientSubscriptions = [];
        $renewals = [];
        $clients = [];
        $pendingApprovalsCount = 0;
        
        try {
            $plans = $planController->getPlansForIndex() ?? [];
            Log::info('Plans fetched: ' . count($plans));
        } catch (\Exception $e) {
            Log::error('Error fetching plans: ' . $e->getMessage());
        }
        
        try {
            $clientSubscriptions = $subscriptionController->getAllSubscriptionsForIndex() ?? [];
            Log::info('Client subscriptions fetched: ' . count($clientSubscriptions));
        } catch (\Exception $e) {
            Log::error('Error fetching subscriptions: ' . $e->getMessage());
        }
        
        try {
            $renewals = $renewalController->getRenewalsForIndex(30) ?? [];
        } catch (\Exception $e) {
            Log::error('Error fetching renewals: ' . $e->getMessage());
        }
        
        try {
            $clients = Client::select('id', 'organization_name', 'primary_contact_email', 'primary_contact_phone')
                ->orderBy('organization_name')
                ->get() ?? [];
            Log::info('Clients fetched: ' . count($clients));
        } catch (\Exception $e) {
            Log::error('Error fetching clients: ' . $e->getMessage());
        }
        
        try {
            if (class_exists('App\Models\DiscountApproval')) {
                $pendingApprovalsCount = \App\Models\DiscountApproval::where('status', 'pending')->count();
                Log::info('Pending approvals: ' . $pendingApprovalsCount);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching pending approvals: ' . $e->getMessage());
        }
        
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
})->name('subscriptions.index');

// ============ SUBSCRIPTION MODULE ROUTES ============
Route::middleware(['auth'])->prefix('subscriptions')->name('subscriptions.')->group(function () {
    
    // ===== SUBSCRIPTION PLANS =====
    Route::get('/plans', [SubscriptionPlanController::class, 'index'])->name('plans.index');
    Route::get('/plans/{subscriptionPlan}', [SubscriptionPlanController::class, 'show'])->name('plans.show');
    Route::post('/plans', [SubscriptionPlanController::class, 'store'])->name('plans.store');
    Route::put('/plans/{subscriptionPlan}', [SubscriptionPlanController::class, 'update'])->name('plans.update');
    Route::delete('/plans/{subscriptionPlan}', [SubscriptionPlanController::class, 'destroy'])->name('plans.destroy');
    Route::post('/plans/{subscriptionPlan}/toggle-status', [SubscriptionPlanController::class, 'toggleStatus'])->name('plans.toggle-status');
    Route::get('/plans/active/list', [SubscriptionPlanController::class, 'activePlans'])->name('plans.active');

    // ===== CLIENT SUBSCRIPTIONS =====
    Route::get('/client-subscriptions/all', [ClientSubscriptionController::class, 'allSubscriptions'])->name('client-subscriptions.all');
    Route::get('/subscription/{id}', [ClientSubscriptionController::class, 'show'])->name('subscription.show');
    Route::post('/assign', [ClientSubscriptionController::class, 'assign'])->name('assign');
    Route::put('/subscription/{id}', [ClientSubscriptionController::class, 'update'])->name('subscription.update');
    Route::delete('/subscription/{id}', [ClientSubscriptionController::class, 'destroy'])->name('subscription.destroy');
    
    // Status change routes
    Route::put('/subscription/{id}/cancel', [ClientSubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::put('/subscription/{id}/suspend', [ClientSubscriptionController::class, 'suspend'])->name('subscription.suspend');
    Route::put('/subscription/{id}/reactivate', [ClientSubscriptionController::class, 'reactivate'])->name('subscription.reactivate');
    Route::put('/subscription/{id}/activate', [ClientSubscriptionController::class, 'activate'])->name('subscription.activate');
    
    // Change plan and discount routes
    Route::post('/subscription/{id}/change-plan', [ClientSubscriptionController::class, 'changePlan'])->name('subscription.change-plan');
    Route::post('/subscription/{id}/apply-discount', [ClientSubscriptionController::class, 'applyDiscount'])->name('subscription.apply-discount');
    
    // Subscription history
    Route::get('/subscription/{id}/history', [ClientSubscriptionController::class, 'history'])->name('subscription.history');

    // ===== RENEWALS =====
    Route::get('/renewals', [RenewalController::class, 'index'])->name('renewals.index');
    Route::get('/renewals/due/{days?}', [RenewalController::class, 'due'])->name('renewals.due');
    Route::post('/renewals/{id}/process', [RenewalController::class, 'process'])->name('renewals.process');

    // ===== DISCOUNT APPROVALS =====
    Route::get('/discount-approvals/pending', [DiscountApprovalController::class, 'pending'])->name('discount-approvals.pending');
    Route::post('/discount-approvals/{id}/approve', [DiscountApprovalController::class, 'approve'])->name('discount-approvals.approve');
    Route::post('/discount-approvals/{id}/reject', [DiscountApprovalController::class, 'reject'])->name('discount-approvals.reject');
    
    // TEMPORARY DEBUG ROUTE - Remove after testing
    Route::get('/discount-approvals/debug/{id}', function($id) {
        try {
            $approval = App\Models\DiscountApproval::with(['subscription.client', 'subscription.plan'])->find($id);
            if (!$approval) {
                return response()->json(['error' => 'Approval not found'], 404);
            }
            
            return response()->json([
                'success' => true,
                'approval' => $approval,
                'subscription' => $approval->subscription,
                'subscription_discount' => $approval->subscription->discount,
                'subscription_discount_status' => $approval->subscription->discount_status,
                'exists' => true,
                'id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    })->name('discount-approvals.debug');

    // ===== PAYMENTS =====
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::post('record', [PaymentController::class, 'record'])->name('record');
        Route::get('subscription/{subscriptionId}', [PaymentController::class, 'history'])->name('history');
        Route::post('{id}/void', [PaymentController::class, 'void'])->name('void');
    });

    // ===== REPORTS =====
    Route::get('reports/renewals', [SubscriptionReportController::class, 'renewals'])->name('reports.renewals');
    Route::get('reports/summary', [SubscriptionReportController::class, 'summary'])->name('reports.summary');
});

// ============ CLIENTS API ROUTES ============
Route::middleware(['auth'])->prefix('clients')->name('clients.')->group(function () {
    Route::get('/list', function () {
        try {
            $clients = Client::select('id', 'organization_name', 'primary_contact_email', 'primary_contact_phone')
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
                ->select('id', 'organization_name', 'primary_contact_email', 'primary_contact_phone')
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
                ->select('id', 'organization_name', 'primary_contact_email', 'primary_contact_phone')
                ->orderBy('organization_name')
                ->get();
            return response()->json($clients);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->name('with-subscriptions');
});

Route::middleware(['auth'])->get('/debug-data', function() {
    try {
        // Get raw database counts first
        $plansCount = DB::table('subscription_plans')->count();
        $subscriptionsCount = DB::table('client_subscriptions')->count();
        $clientsCount = DB::table('clients')->count();
        
        // Then try to get the actual data
        $plans = [];
        $subscriptions = [];
        $clients = [];
        
        try {
            $plans = DB::table('subscription_plans')->get();
        } catch (\Exception $e) {
            \Log::error('Error fetching plans: ' . $e->getMessage());
        }
        
        try {
            $subscriptions = DB::table('client_subscriptions')
                ->leftJoin('clients', 'client_subscriptions.client_id', '=', 'clients.id')
                ->leftJoin('subscription_plans', 'client_subscriptions.plan_id', '=', 'subscription_plans.id')
                ->select(
                    'client_subscriptions.*',
                    'clients.organization_name as client_name',
                    'clients.primary_contact_email',
                    'subscription_plans.plan_name'
                )
                ->get();
        } catch (\Exception $e) {
            \Log::error('Error fetching subscriptions: ' . $e->getMessage());
        }
        
        try {
            $clients = DB::table('clients')->get();
        } catch (\Exception $e) {
            \Log::error('Error fetching clients: ' . $e->getMessage());
        }
        
        return response()->json([
            'success' => true,
            'counts' => [
                'plans' => $plansCount,
                'subscriptions' => $subscriptionsCount,
                'clients' => $clientsCount
            ],
            'data' => [
                'plans' => $plans,
                'subscriptions' => $subscriptions,
                'clients' => $clients
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('debug.data');

// ============ DASHBOARD ROUTE ============
Route::middleware(['auth'])->get('/dashboard', function () {
    return Inertia\Inertia::render('Dashboard');
})->name('dashboard');

// ============ AUTH ROUTES ============
require __DIR__.'/auth.php';