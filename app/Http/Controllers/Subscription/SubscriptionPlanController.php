<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\Client;
use App\Models\DiscountApproval;
use App\Services\Subscription\SubscriptionService;
use App\Http\Requests\Subscription\StoreSubscriptionPlanRequest;
use App\Http\Requests\Subscription\UpdateSubscriptionPlanRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class SubscriptionPlanController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Main subscriptions dashboard page
     */
    public function subscriptionsIndex(Request $request)
    {
        try {
            $planController = app(SubscriptionPlanController::class);
            $subscriptionController = app(ClientSubscriptionController::class);
            $renewalController = app(RenewalController::class);

            $plans = $planController->getPlansForIndex() ?? [];
            $clientSubscriptions = $subscriptionController->getAllSubscriptionsForIndex() ?? [];
            $renewals = $renewalController->getRenewalsForIndex(30) ?? [];
            
            // Updated client query to use actual column names
            $clients = Client::select('id', 'organization_name', 'client_code', 'status')
                ->orderBy('organization_name')
                ->get()
                ->map(function ($client) {
                    return [
                        'id' => $client->id,
                        'organization_name' => $client->organization_name,
                        'primary_contact_email' => $client->client_code . '@example.com', // Placeholder until you add contact fields
                        'primary_contact_phone' => 'Not available', // Placeholder until you add contact fields
                        'client_code' => $client->client_code,
                        'status' => $client->status,
                    ];
                });

            $pendingApprovalsCount = class_exists(DiscountApproval::class)
                ? DiscountApproval::where('status', 'pending')->count()
                : 0;

            return Inertia::render('Subscriptions/Index', [
                'plans' => $plans,
                'clientSubscriptions' => $clientSubscriptions,
                'renewals' => $renewals,
                'clients' => $clients,
                'filters' => $request->all(),
                'pendingApprovalsCount' => $pendingApprovalsCount,
                'error' => null
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading subscriptions page: ' . $e->getMessage());
            
            return Inertia::render('Subscriptions/Index', [
                'plans' => [],
                'clientSubscriptions' => [],
                'renewals' => [],
                'clients' => [],
                'filters' => $request->all(),
                'pendingApprovalsCount' => 0,
                'error' => 'Failed to load subscription data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get plans for the main index page
     */
    public function getPlansForIndex()
    {
        try {
            $plans = SubscriptionPlan::query()
                ->withCount(['subscriptions as active_subscriptions_count' => function ($query) {
                    $query->whereIn('status', ['active', 'trial']);
                }])
                ->orderBy('created_at', 'desc')
                ->get();
            
            Log::info('getPlansForIndex found: ' . $plans->count() . ' plans');
            
            return $plans;
        } catch (\Exception $e) {
            Log::error('Error in getPlansForIndex: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Display list of subscription plans
     */
    public function index(Request $request)
    {
        try {
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

            $plans = $query->paginate($request->per_page ?? 15)->withQueryString();

            // For Inertia requests
            if ($request->header('X-Inertia')) {
                return Inertia::render('Subscriptions/Plans/Index', [
                    'plans' => $plans,
                    'filters' => $request->only(['search', 'status', 'sort', 'direction']),
                ]);
            }

            // For API requests
            return response()->json([
                'data' => $plans->items(),
                'meta' => [
                    'total' => $plans->total(),
                    'per_page' => $plans->perPage(),
                    'current_page' => $plans->currentPage(),
                    'last_page' => $plans->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in index: ' . $e->getMessage());
            
            if ($request->header('X-Inertia')) {
                return Inertia::render('Subscriptions/Plans/Index', [
                    'plans' => ['data' => [], 'meta' => []],
                    'filters' => $request->only(['search', 'status', 'sort', 'direction']),
                    'error' => 'Failed to load plans: ' . $e->getMessage()
                ]);
            }
            
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all active plans for dropdown
     */
    public function activePlans(Request $request)
    {
        try {
            $plans = SubscriptionPlan::where('is_active', true)
                ->orderBy('plan_name')
                ->get(['id', 'plan_name', 'price', 'billing_cycle', 'trial_days']);
            
            return response()->json($plans);
        } catch (\Exception $e) {
            Log::error('Error in activePlans: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store new plan
     */
    public function store(StoreSubscriptionPlanRequest $request)
    {
        try {
            $validatedData = $request->validated();
            
            // Ensure boolean values are properly cast
            $validatedData['is_active'] = isset($validatedData['is_active']) ? filter_var($validatedData['is_active'], FILTER_VALIDATE_BOOLEAN) : true;
            
            // Add created_by_user_id from authenticated user
            $validatedData['created_by_user_id'] = auth()->id();
            
            $plan = $this->subscriptionService->createPlan($validatedData);
            
            // Load the active_subscriptions_count
            $plan->loadCount(['subscriptions as active_subscriptions_count' => function ($query) {
                $query->whereIn('status', ['active', 'trial']);
            }]);

            // For Inertia requests
            if ($request->header('X-Inertia')) {
                return redirect()->route('subscriptions.index')
                    ->with('success', 'Subscription plan created successfully.');
            }

            return response()->json([
                'success' => true,
                'message' => 'Plan created successfully',
                'plan' => $plan
            ]);
                
        } catch (\Exception $e) {
            Log::error('Error creating plan: ' . $e->getMessage());
            
            if ($request->header('X-Inertia')) {
                return redirect()->back()
                    ->with('error', 'Failed to create plan: ' . $e->getMessage())
                    ->withInput();
            }
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display plan details
     */
    public function show(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        try {
            $subscriptionPlan->load([
                'subscriptions' => function ($query) {
                    $query->with('client')
                        ->latest()
                        ->limit(10);
                }
            ]);
            
            $subscriptionPlan->loadCount(['subscriptions as active_subscriptions_count' => function ($query) {
                $query->whereIn('status', ['active', 'trial']);
            }]);

            if ($request->header('X-Inertia')) {
                return Inertia::render('Subscriptions/Plans/Show', [
                    'plan' => $subscriptionPlan,
                ]);
            }

            return response()->json($subscriptionPlan);
        } catch (\Exception $e) {
            Log::error('Error in show: ' . $e->getMessage());
            
            if ($request->header('X-Inertia')) {
                return redirect()->route('subscriptions.index')
                    ->with('error', 'Failed to load plan: ' . $e->getMessage());
            }
            
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update plan
     */
    public function update(UpdateSubscriptionPlanRequest $request, SubscriptionPlan $subscriptionPlan)
    {
        try {
            $validatedData = $request->validated();
            
            // Ensure boolean values are properly cast
            if (isset($validatedData['is_active'])) {
                $validatedData['is_active'] = filter_var($validatedData['is_active'], FILTER_VALIDATE_BOOLEAN);
            }
            
            $plan = $this->subscriptionService->updatePlan($subscriptionPlan->id, $validatedData);
            
            $plan->loadCount(['subscriptions as active_subscriptions_count' => function ($query) {
                $query->whereIn('status', ['active', 'trial']);
            }]);

            if ($request->header('X-Inertia')) {
                return redirect()->route('subscriptions.index')
                    ->with('success', 'Subscription plan updated successfully.');
            }

            return response()->json([
                'success' => true,
                'message' => 'Plan updated successfully',
                'plan' => $plan
            ]);
                
        } catch (\Exception $e) {
            Log::error('Error updating plan: ' . $e->getMessage());
            
            if ($request->header('X-Inertia')) {
                return redirect()->back()
                    ->with('error', 'Failed to update plan: ' . $e->getMessage())
                    ->withInput();
            }
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete plan
     */
    public function destroy(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        try {
            $this->subscriptionService->deletePlan($subscriptionPlan->id);
            
            if ($request->header('X-Inertia')) {
                return redirect()->route('subscriptions.index')
                    ->with('success', 'Subscription plan deleted successfully.');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Plan deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting plan: ' . $e->getMessage());
            
            if ($request->header('X-Inertia')) {
                return redirect()->route('subscriptions.index')
                    ->with('error', $e->getMessage());
            }
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle plan active status
     */
    public function toggleStatus(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        try {
            $subscriptionPlan->is_active = !$subscriptionPlan->is_active;
            $subscriptionPlan->save();
            
            $subscriptionPlan->loadCount(['subscriptions as active_subscriptions_count' => function ($query) {
                $query->whereIn('status', ['active', 'trial']);
            }]);
            
            // Only return Inertia response for Inertia requests
            if ($request->header('X-Inertia')) {
                return redirect()->back()->with('success', 'Plan status updated successfully.');
            }
            
            // For any other type of request, return a proper error
            return response()->json(['error' => 'Invalid request type'], 400);
            
        } catch (\Exception $e) {
            Log::error('Error toggling plan status: ' . $e->getMessage());
            
            if ($request->header('X-Inertia')) {
                return redirect()->back()->with('error', $e->getMessage());
            }
            
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}