<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\ClientSubscription;
use App\Services\Subscription\SubscriptionService;
use App\Http\Requests\Subscription\RecordPaymentRequest;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Check permission helper
     */
    private function checkPermission($permission)
    {
        if (!auth()->user()->can($permission)) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * Record a payment
     */
    public function record(RecordPaymentRequest $request)
    {
        $this->checkPermission('record payments');

        try {
            $payment = $this->subscriptionService->recordPayment(
                $request->subscription_id,
                $request->validated()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'payment' => $payment
                ]);
            }

            return redirect()->route('subscriptions.subscription.show', $request->subscription_id)
                ->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Get payment history for subscription
     */
    public function history($subscriptionId)
    {
        $this->checkPermission('view payment history');

        $subscription = ClientSubscription::findOrFail($subscriptionId);
        
        $payments = $this->subscriptionService->getPaymentHistory($subscriptionId);

        return response()->json([
            'subscription' => $subscription,
            'payments' => $payments,
        ]);
    }
}