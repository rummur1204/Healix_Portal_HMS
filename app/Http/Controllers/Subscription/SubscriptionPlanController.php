<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Services\Subscription\SubscriptionService;
use App\Http\Requests\Subscription\StoreSubscriptionPlanRequest;
use App\Http\Requests\Subscription\UpdateSubscriptionPlanRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionPlanController extends Controller
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
     * Display list of subscription plans
     */
    public function index(Request $request)
    {
        $this->checkPermission('view subscription plans');

        $query = SubscriptionPlan::query()
            ->withCount(['subscriptions as active_subscriptions_count' => function ($query) {
                $query->whereIn('status', ['active', 'trial']);
            }]);

        // Apply filters
        if ($request->has('search') && !empty($request->search)) {
            $query->where('plan_name', 'like', "%{$request->search}%");
        }
        
        if ($request->has('status') && $request->status !== '') {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        // Apply sorting
        $sortField = $request->sort ?? 'created_at';
        $sortDirection = $request->direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        // Check if it's an API request (from fetch in Vue)
        if ($request->wantsJson() || $request->ajax()) {
            $plans = $query->get(); // Return all for API
            return response()->json([
                'data' => $plans
            ]);
        }

        // For Inertia requests, return paginated data
        $plans = $query->paginate($request->per_page ?? 15)->withQueryString();

        return Inertia::render('Subscriptions/Plans/Index', [
            'plans' => $plans,
            'filters' => $request->only(['search', 'status', 'sort', 'direction']),
            'billingCycles' => [
                'monthly' => 'Monthly',
                'quarterly' => 'Quarterly',
                'yearly' => 'Yearly',
                'custom' => 'Custom',
            ],
            'can' => [
                'create' => auth()->user()->can('create subscription plans'),
                'edit' => auth()->user()->can('edit subscription plans'),
                'delete' => auth()->user()->can('delete subscription plans'),
            ]
        ]);
    }

    /**
     * Get all active plans for dropdown
     */
    public function activePlans(Request $request)
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('plan_name')
            ->get(['id', 'plan_name', 'price', 'billing_cycle', 'trial_days']);
        
        return response()->json($plans);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $this->checkPermission('create subscription plans');

        return Inertia::render('Subscriptions/Plans/Form', [
            'billingCycles' => [
                ['value' => 'monthly', 'label' => 'Monthly'],
                ['value' => 'quarterly', 'label' => 'Quarterly'],
                ['value' => 'yearly', 'label' => 'Yearly'],
                ['value' => 'custom', 'label' => 'Custom'],
            ],
            'supportLevels' => [
                ['value' => 'standard', 'label' => 'Standard'],
                ['value' => 'premium', 'label' => 'Premium'],
            ]
        ]);
    }

    /**
     * Store new plan
     */
    public function store(StoreSubscriptionPlanRequest $request)
    {
        $this->checkPermission('create subscription plans');

        $plan = $this->subscriptionService->createPlan($request->validated());
        
        // Load the active_subscriptions_count
        $plan->loadCount(['subscriptions as active_subscriptions_count' => function ($query) {
            $query->whereIn('status', ['active', 'trial']);
        }]);

        // Check if it's an API request (from fetch in Vue)
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($plan, 201);
        }

        return redirect()->route('subscriptions.plans.index')
            ->with('success', 'Subscription plan created successfully.');
    }

    /**
     * Display plan details
     */
    public function show(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $this->checkPermission('view subscription plans');

        $subscriptionPlan->load(['subscriptions' => function ($query) {
            $query->with('client')->latest()->limit(10);
        }]);

        // Check if it's an API request (from fetch in Vue)
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($subscriptionPlan);
        }

        return Inertia::render('Subscriptions/Plans/Show', [
            'plan' => $subscriptionPlan,
        ]);
    }

    /**
     * Show edit form
     */
    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        $this->checkPermission('edit subscription plans');

        return Inertia::render('Subscriptions/Plans/Form', [
            'plan' => $subscriptionPlan,
            'billingCycles' => [
                ['value' => 'monthly', 'label' => 'Monthly'],
                ['value' => 'quarterly', 'label' => 'Quarterly'],
                ['value' => 'yearly', 'label' => 'Yearly'],
                ['value' => 'custom', 'label' => 'Custom'],
            ],
            'supportLevels' => [
                ['value' => 'standard', 'label' => 'Standard'],
                ['value' => 'premium', 'label' => 'Premium'],
            ]
        ]);
    }

    /**
     * Update plan
     */
    public function update(UpdateSubscriptionPlanRequest $request, SubscriptionPlan $subscriptionPlan)
    {
        $this->checkPermission('edit subscription plans');

        $plan = $this->subscriptionService->updatePlan($subscriptionPlan->id, $request->validated());
        
        // Load the active_subscriptions_count
        $plan->loadCount(['subscriptions as active_subscriptions_count' => function ($query) {
            $query->whereIn('status', ['active', 'trial']);
        }]);

        // Check if it's an API request (from fetch in Vue)
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($plan);
        }

        return redirect()->route('subscriptions.plans.show', $plan->id)
            ->with('success', 'Subscription plan updated successfully.');
    }

    /**
     * Delete plan
     */
    public function destroy(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $this->checkPermission('delete subscription plans');

        try {
            $this->subscriptionService->deletePlan($subscriptionPlan->id);
            
            // Check if it's an API request (from fetch in Vue)
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Plan deleted successfully']);
            }
            
            return redirect()->route('subscriptions.plans.index')
                ->with('success', 'Subscription plan deleted successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            
            return redirect()->route('subscriptions.plans.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle plan active status
     */
    public function toggleStatus(Request $request, $id)
    {
        $this->checkPermission('edit subscription plans');

        try {
            $plan = $this->subscriptionService->togglePlanStatus($id);
            
            // Check if it's an API request (from fetch in Vue)
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Plan status updated successfully',
                    'is_active' => $plan->is_active
                ]);
            }

            return redirect()->back()
                ->with('success', 'Plan status updated successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}