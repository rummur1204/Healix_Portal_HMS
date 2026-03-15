<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\ClientSubscription;
use App\Models\Client;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionHistory;
use App\Models\DiscountApproval;
use App\Services\Subscription\SubscriptionService;
use App\Http\Requests\Subscription\AssignSubscriptionRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ClientSubscriptionController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function getAllSubscriptionsForIndex()
    {
        try {
            $subscriptions = ClientSubscription::with(['client', 'plan'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            Log::info('getAllSubscriptionsForIndex found: ' . $subscriptions->count() . ' subscriptions');
            
            // Add pending discount information to each subscription
            foreach ($subscriptions as $subscription) {
                // Check for pending discount approvals
                $pendingDiscount = DiscountApproval::where('subscription_id', $subscription->id)
                    ->where('status', 'pending')
                    ->first();
                
                if ($pendingDiscount) {
                    $subscription->pending_discount = $pendingDiscount->discount_amount;
                    $subscription->pending_discount_percentage = $pendingDiscount->discount_percentage;
                    Log::info('Added pending discount to subscription ' . $subscription->id . ': $' . $pendingDiscount->discount_amount);
                }
                
                // Log discount status for debugging
                Log::debug('Subscription ' . $subscription->id . ' - Discount: $' . $subscription->discount . ', Status: ' . ($subscription->discount_status ?? 'none'));
            }
            
            return $subscriptions;
        } catch (\Exception $e) {
            Log::error('Error in getAllSubscriptionsForIndex: ' . $e->getMessage());
            return [];
        }
    }

    public function allSubscriptions(Request $request)
    {
        try {
            $query = ClientSubscription::with(['client', 'plan'])
                ->orderBy('created_at', 'desc');

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->whereHas('client', function ($cq) use ($search) {
                        $cq->where('organization_name', 'like', "%{$search}%")
                          ->orWhere('primary_contact_email', 'like', "%{$search}%");
                    })->orWhere('subscription_id', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('plan_id')) {
                $query->where('plan_id', $request->plan_id);
            }

            $subscriptions = $query->paginate($request->per_page ?? 20);

            // Add pending discount information
            foreach ($subscriptions as $subscription) {
                $pendingDiscount = DiscountApproval::where('subscription_id', $subscription->id)
                    ->where('status', 'pending')
                    ->first();
                
                if ($pendingDiscount) {
                    $subscription->pending_discount = $pendingDiscount->discount_amount;
                    $subscription->pending_discount_percentage = $pendingDiscount->discount_percentage;
                }
            }

            return response()->json([
                'data' => $subscriptions->items(),
                'meta' => [
                    'total' => $subscriptions->total(),
                    'per_page' => $subscriptions->perPage(),
                    'current_page' => $subscriptions->currentPage(),
                    'last_page' => $subscriptions->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in allSubscriptions: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show subscription details
     */
    public function show($id, Request $request)
    {
        try {
            Log::info('Loading subscription details', ['id' => $id]);
            
            $subscription = ClientSubscription::with([
                'client', 
                'plan', 
                'history' => function ($query) {
                    $query->with('performedBy', 'oldPlan', 'newPlan')->latest();
                }, 
                'payments',
                'discountApprovals' => function ($query) {
                    $query->with('requestedBy', 'approvedBy')->latest();
                }
            ])->findOrFail($id);

            // Get pending discount if any
            $pendingDiscount = DiscountApproval::where('subscription_id', $id)
                ->where('status', 'pending')
                ->first();
            
            // Get discount approvals history
            $discountApprovals = $subscription->discountApprovals->take(5);

            // Define permissions for the current user
            $can = [
                'cancel' => auth()->user()->can('cancel subscriptions') ?? true,
                'suspend' => auth()->user()->can('suspend subscriptions') ?? true,
                'reactivate' => auth()->user()->can('reactivate subscriptions') ?? true,
                'process_renewal' => auth()->user()->can('process renewals') ?? true,
                'record_payment' => auth()->user()->can('record payments') ?? true,
                'upgrade_downgrade' => auth()->user()->can('change subscription plan') ?? true,
                'approve_discount' => auth()->user()->can('approve discounts') ?? false,
            ];

            Log::info('Subscription loaded successfully', [
                'id' => $id,
                'discount' => $subscription->discount,
                'discount_status' => $subscription->discount_status
            ]);

            return Inertia::render('Subscriptions/ClientSubscriptions/Show', [
                'subscription' => $subscription,
                'can' => $can,
                'pendingDiscount' => $pendingDiscount,
                'discountApprovals' => $discountApprovals
            ]);
        } catch (\Exception $e) {
            Log::error('Error in show: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('subscriptions.index')
                ->with('error', 'Failed to load subscription: ' . $e->getMessage());
        }
    }

    /**
     * Assign subscription to client
     */
    public function assign(AssignSubscriptionRequest $request)
    {
        Log::info('========== ASSIGN METHOD STARTED ==========');
        Log::info('Request data:', $request->all());
        
        try {
            DB::beginTransaction();

            // Get client and plan
            $client = Client::find($request->client_id);
            if (!$client) {
                Log::error('Client not found with ID: ' . $request->client_id);
                throw new \Exception('Client not found with ID: ' . $request->client_id);
            }

            $plan = SubscriptionPlan::find($request->plan_id);
            if (!$plan) {
                $availablePlans = SubscriptionPlan::pluck('id')->toArray();
                Log::error('Plan not found with ID: ' . $request->plan_id);
                throw new \Exception('Plan not found with ID: ' . $request->plan_id . '. Available plan IDs: ' . implode(', ', $availablePlans));
            }

            Log::info('Client found:', ['id' => $client->id, 'name' => $client->organization_name]);
            Log::info('Plan found:', ['id' => $plan->id, 'name' => $plan->plan_name, 'price' => $plan->price]);

            // Generate unique subscription ID
            $subscriptionId = 'SUB-' . strtoupper(Str::random(8));
            
            // Calculate dates
            $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now();
            
            // Set end date based on billing cycle
            switch ($plan->billing_cycle) {
                case 'monthly':
                    $endDate = $startDate->copy()->addMonth();
                    break;
                case 'quarterly':
                    $endDate = $startDate->copy()->addMonths(3);
                    break;
                case 'yearly':
                    $endDate = $startDate->copy()->addYear();
                    break;
                default:
                    $endDate = $startDate->copy()->addMonth();
            }

            // Check for trial days
            $trialEndDate = null;
            $status = 'active';
            if ($plan->trial_days > 0) {
                $trialEndDate = Carbon::now()->addDays($plan->trial_days);
                $status = 'trial';
            }

            Log::info('Creating subscription with data:', [
                'subscription_id' => $subscriptionId,
                'client_id' => $client->id,
                'plan_id' => $plan->id,
                'start_date' => $startDate->toDateTimeString(),
                'end_date' => $endDate->toDateTimeString(),
                'trial_end_date' => $trialEndDate,
                'status' => $status,
                'payment_method' => $request->payment_method
            ]);

            // Create subscription
            $subscription = ClientSubscription::create([
                'subscription_id' => $subscriptionId,
                'client_id' => $client->id,
                'plan_id' => $plan->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'trial_end_date' => $trialEndDate,
                'status' => $status,
                'payment_method' => $request->payment_method,
                'payment_status' => 'unpaid',
                'discount' => 0,
                'discount_status' => 'none',
                'created_by_user_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            Log::info('Subscription created:', ['id' => $subscription->id, 'subscription_id' => $subscription->subscription_id]);

            // Handle discount if provided in the request
            $discountNote = '';
            if ($request->has('discount') && $request->discount > 0) {
                $requiresApproval = $this->subscriptionService->requiresDiscountApproval($request->discount, $plan->price);
                
                if ($requiresApproval) {
                    // Create approval request
                    $this->subscriptionService->requestDiscountApproval(
                        $subscription->id, 
                        $request->discount, 
                        $request->discount_reason ?? null
                    );
                    
                    $discountNote = " Discount of $$request->discount requires approval.";
                    
                    Log::info('Discount approval requested', [
                        'subscription_id' => $subscription->id,
                        'discount' => $request->discount
                    ]);
                } else {
                    $subscription->update([
                        'discount' => $request->discount,
                        'discount_status' => 'approved'
                    ]);
                    
                    $discountNote = " Discount of $$request->discount applied.";
                    
                    Log::info('Discount applied automatically', [
                        'subscription_id' => $subscription->id,
                        'discount' => $request->discount
                    ]);
                }
            }

            // Add history
            SubscriptionHistory::create([
                'subscription_id' => $subscription->id,
                'client_id' => $client->id,
                'action' => 'assigned',
                'old_plan_id' => null,
                'new_plan_id' => $plan->id,
                'old_status' => null,
                'new_status' => $status,
                'notes' => 'Subscription assigned to client by ' . (auth()->user()->name ?? 'System') . $discountNote,
                'performed_by_user_id' => auth()->id() ?? 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            Log::info('========== ASSIGN METHOD COMPLETED SUCCESSFULLY ==========');
            Log::info('New subscription ID: ' . $subscription->id);

            $message = 'Subscription assigned successfully!';
            if ($request->has('discount') && $request->discount > 0 && $this->subscriptionService->requiresDiscountApproval($request->discount, $plan->price)) {
                $message = 'Subscription assigned. Discount requires approval.';
            }

            return redirect()->route('subscriptions.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('========== ASSIGN METHOD FAILED ==========');
            Log::error('Error: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                ->with('error', 'Failed to assign subscription: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Change subscription plan (upgrade/downgrade)
     */
    public function changePlan(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'plan_id' => 'required|exists:subscription_plans,id',
                'effective_date' => 'sometimes|in:immediate,next_renewal'
            ]);

            $subscription = $this->subscriptionService->changePlan(
                $id,
                $validated['plan_id'],
                $validated['effective_date'] ?? 'immediate'
            );

            return redirect()->route('subscriptions.subscription.show', $subscription->id)
                ->with('success', 'Plan changed successfully.');
        } catch (\Exception $e) {
            Log::error('Error changing plan: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Apply discount with approval check
     */
    public function applyDiscount(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'discount' => 'required|numeric|min:0',
                'reason' => 'nullable|string'
            ]);

            $subscription = ClientSubscription::with('plan')->findOrFail($id);
            
            $requiresApproval = $this->subscriptionService->requiresDiscountApproval(
                $validated['discount'], 
                $subscription->plan->price
            );
            
            if ($requiresApproval) {
                $approval = $this->subscriptionService->requestDiscountApproval(
                    $id, 
                    $validated['discount'], 
                    $validated['reason'] ?? null
                );
                
                return redirect()->route('subscriptions.subscription.show', $id)
                    ->with('success', 'Discount submitted for approval.');
            } else {
                $subscription->update([
                    'discount' => $validated['discount'],
                    'discount_status' => 'approved'
                ]);
                
                $this->subscriptionService->addHistory(
                    $subscription,
                    'discount_applied',
                    ['discount' => $validated['discount']],
                    "Discount of $$validated[discount] applied by " . (auth()->user()->name ?? 'System')
                );
                
                return redirect()->route('subscriptions.subscription.show', $id)
                    ->with('success', 'Discount applied successfully.');
            }
        } catch (\Exception $e) {
            Log::error('Error applying discount: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $subscription = ClientSubscription::with(['client', 'plan'])->findOrFail($id);
            
            $validated = $request->validate([
                'client_id' => 'sometimes|required|exists:clients,id',
                'plan_id' => 'sometimes|required|exists:subscription_plans,id',
                'start_date' => 'sometimes|required|date',
                'end_date' => 'sometimes|required|date|after:start_date',
                'status' => 'sometimes|required|in:active,trial,past_due,suspended,cancelled',
                'payment_method' => 'nullable|string',
                'payment_status' => 'nullable|in:paid,unpaid,partial',
                'discount' => 'nullable|numeric|min:0',
                'discount_status' => 'nullable|in:none,pending_approval,approved,rejected'
            ]);

            $changes = [];
            foreach ($validated as $field => $value) {
                if ($subscription->$field != $value) {
                    $changes[$field] = ['old' => $subscription->$field, 'new' => $value];
                }
            }

            $subscription->update($validated);
            
            if (!empty($changes)) {
                $this->subscriptionService->addHistory(
                    $subscription, 
                    'updated', 
                    $changes,
                    'Subscription updated by ' . (auth()->user()->name ?? 'System')
                );
            }

            $subscription->load(['client', 'plan']);

            return redirect()->route('subscriptions.subscription.show', $subscription->id)
                ->with('success', 'Subscription updated successfully.');
                
        } catch (\Exception $e) {
            Log::error('Error updating subscription: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $subscription = ClientSubscription::findOrFail($id);
            
            SubscriptionHistory::create([
                'subscription_id' => $subscription->id,
                'client_id' => $subscription->client_id,
                'action' => 'deleted',
                'old_plan_id' => $subscription->plan_id,
                'old_status' => $subscription->status,
                'notes' => 'Subscription deleted by ' . (auth()->user()->name ?? 'System'),
                'performed_by_user_id' => auth()->id() ?? 1
            ]);
            
            $subscription->delete();

            return redirect()->route('subscriptions.index')
                ->with('success', 'Subscription deleted successfully.');
                
        } catch (\Exception $e) {
            Log::error('Error deleting subscription: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function cancel(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'reason' => 'required|string'
            ]);

            $subscription = $this->subscriptionService->cancelSubscription(
                $id,
                $validated['reason']
            );

            return redirect()->route('subscriptions.subscription.show', $subscription->id)
                ->with('success', 'Subscription cancelled successfully.');
                
        } catch (\Exception $e) {
            Log::error('Error cancelling subscription: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function suspend(Request $request, $id)
    {
        try {
            $validated = $request->validate(['reason' => 'required|string']);

            $subscription = $this->subscriptionService->suspendSubscription(
                $id,
                $validated['reason']
            );

            return redirect()->route('subscriptions.subscription.show', $subscription->id)
                ->with('success', 'Subscription suspended successfully.');
                
        } catch (\Exception $e) {
            Log::error('Error suspending subscription: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function reactivate(Request $request, $id)
    {
        try {
            $subscription = $this->subscriptionService->reactivateSubscription($id);

            return redirect()->route('subscriptions.subscription.show', $subscription->id)
                ->with('success', 'Subscription reactivated successfully.');
                
        } catch (\Exception $e) {
            Log::error('Error reactivating subscription: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function activate(Request $request, $id)
    {
        try {
            $subscription = ClientSubscription::findOrFail($id);
            $oldStatus = $subscription->status;
            $subscription->update(['status' => 'active']);

            $this->subscriptionService->addHistory(
                $subscription, 
                'activated', 
                ['status' => ['old' => $oldStatus, 'new' => 'active']],
                'Subscription activated by ' . (auth()->user()->name ?? 'System')
            );

            return redirect()->route('subscriptions.subscription.show', $subscription->id)
                ->with('success', 'Subscription activated successfully.');
                
        } catch (\Exception $e) {
            Log::error('Error activating subscription: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function history($id, Request $request)
    {
        try {
            $subscription = ClientSubscription::withTrashed()->findOrFail($id);
            
            $history = $subscription->history()
                ->with(['performedBy', 'oldPlan', 'newPlan'])
                ->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 20);

            return response()->json([
                'data' => $history->items(),
                'meta' => [
                    'total' => $history->total(),
                    'per_page' => $history->perPage(),
                    'current_page' => $history->currentPage(),
                    'last_page' => $history->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}