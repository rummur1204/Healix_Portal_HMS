<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Services\Subscription\SubscriptionService;
use App\Http\Requests\Subscription\ProcessRenewalRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RenewalController extends Controller
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
     * Show renewals dashboard
     */
    public function index()
    {
        $this->checkPermission('view renewals report');

        return Inertia::render('Subscriptions/Renewals/Index', [
            'stats' => [
                'due_today' => $this->subscriptionService->getRenewalsDue(0)->count(),
                'due_7_days' => $this->subscriptionService->getRenewalsDue(7)->count(),
                'due_15_days' => $this->subscriptionService->getRenewalsDue(15)->count(),
                'due_30_days' => $this->subscriptionService->getRenewalsDue(30)->count(),
            ]
        ]);
    }

    /**
     * Get renewals due in X days
     */
    public function due($days = 30, Request $request)
    {
        $this->checkPermission('view renewals report');

        $renewals = $this->subscriptionService->getRenewalsDue($days);

        if ($request->wantsJson()) {
            return response()->json($renewals);
        }

        return Inertia::render('Subscriptions/Renewals/Due', [
            'renewals' => $renewals,
            'days' => $days,
        ]);
    }

    /**
     * Process a renewal
     */
    public function process($id, ProcessRenewalRequest $request)
    {
        $this->checkPermission('process renewals');

        try {
            $newSubscription = $this->subscriptionService->processRenewal($id);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'subscription' => $newSubscription
                ]);
            }

            return redirect()->route('subscriptions.subscription.show', $newSubscription->id)
                ->with('success', 'Renewal processed successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}