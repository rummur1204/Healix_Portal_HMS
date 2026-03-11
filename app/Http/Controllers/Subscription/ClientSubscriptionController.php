<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\ClientSubscription;
use App\Models\Client;
use App\Services\Subscription\SubscriptionService;
use App\Http\Requests\Subscription\AssignSubscriptionRequest;
use App\Http\Requests\Subscription\CancelSubscriptionRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientSubscriptionController extends Controller
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
     * Get all client subscriptions for the tab
     */
    public function allSubscriptions(Request $request)
    {
        $this->checkPermission('view client subscriptions');

        $subscriptions = ClientSubscription::with(['client', 'plan'])
            ->orderBy('created_at', 'desc')
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->plan_id, function ($query, $planId) {
                $query->where('plan_id', $planId);
            })
            ->when($request->search, function ($query, $search) {
                $query->whereHas('client', function ($q) use ($search) {
                    $q->where('organization_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('subscription_id', 'like', "%{$search}%");
            })
            ->paginate($request->per_page ?? 20);

        return response()->json($subscriptions);
    }

    /**
     * Get all subscriptions for a client
     */
    public function clientSubscriptions($clientId)
    {
        $this->checkPermission('view client subscriptions');

        $client = Client::findOrFail($clientId);
        
        $subscriptions = $client->subscriptions()
            ->with('plan')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Subscriptions/ClientSubscriptions/Index', [
            'client' => $client,
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * Show subscription details
     */
    public function show($id)
    {
        $this->checkPermission('view client subscriptions');

        $subscription = ClientSubscription::with([
            'client', 
            'plan', 
            'history' => function ($query) {
                $query->with('performedBy')->latest();
            }, 
            'payments'
        ])->findOrFail($id);

        return Inertia::render('Subscriptions/ClientSubscriptions/Show', [
            'subscription' => $subscription,
            'can' => [
                'cancel' => auth()->user()->can('cancel subscriptions'),
                'suspend' => auth()->user()->can('edit subscriptions'),
                'reactivate' => auth()->user()->can('edit subscriptions'),
                'record_payment' => auth()->user()->can('record payments'),
                'process_renewal' => auth()->user()->can('process renewals'),
            ]
        ]);
    }

    /**
     * Assign subscription to client
     */
    public function assign(AssignSubscriptionRequest $request)
    {
        $this->checkPermission('assign subscriptions');

        try {
            $subscription = $this->subscriptionService->assignSubscription(
                $request->client_id,
                $request->plan_id,
                $request->validated()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'subscription' => $subscription
                ]);
            }

            return redirect()->route('subscriptions.subscription.show', $subscription->id)
                ->with('success', 'Subscription assigned successfully.');
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
     * Cancel subscription
     */
    public function cancel($id, CancelSubscriptionRequest $request)
    {
        $this->checkPermission('cancel subscriptions');

        try {
            $subscription = $this->subscriptionService->cancelSubscription(
                $id,
                $request->reason
            );

            return redirect()->route('subscriptions.subscription.show', $subscription->id)
                ->with('success', 'Subscription cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Suspend subscription
     */
    public function suspend($id, Request $request)
    {
        $this->checkPermission('edit subscriptions');

        $request->validate(['reason' => 'required|string']);

        try {
            $subscription = $this->subscriptionService->suspendSubscription(
                $id,
                $request->reason
            );

            return redirect()->route('subscriptions.subscription.show', $subscription->id)
                ->with('success', 'Subscription suspended successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Reactivate subscription
     */
    public function reactivate($id)
    {
        $this->checkPermission('edit subscriptions');

        try {
            $subscription = $this->subscriptionService->reactivateSubscription($id);

            return redirect()->route('subscriptions.subscription.show', $subscription->id)
                ->with('success', 'Subscription reactivated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Get subscription history
     */
    public function history($id)
    {
        $this->checkPermission('view client subscriptions');

        $subscription = ClientSubscription::findOrFail($id);
        
        $history = $subscription->history()
            ->with(['performedBy', 'oldPlan', 'newPlan'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($history);
    }
}