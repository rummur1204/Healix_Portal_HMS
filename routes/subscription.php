<?php

use App\Http\Controllers\Subscription\SubscriptionPlanController;
use App\Http\Controllers\Subscription\ClientSubscriptionController;
use App\Http\Controllers\Subscription\RenewalController;
use App\Http\Controllers\Subscription\PaymentController;
use App\Http\Controllers\Subscription\SubscriptionReportController;
use App\Http\Controllers\Subscription\DiscountApprovalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SUBSCRIPTIONS MODULE
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('subscriptions')->name('subscriptions.')->group(function () {
    // Main subscription page
    Route::get('/', [SubscriptionPlanController::class, 'subscriptionsIndex'])->name('index');
    
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