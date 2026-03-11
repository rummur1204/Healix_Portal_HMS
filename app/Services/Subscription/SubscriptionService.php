<?php

namespace App\Services\Subscription;

use App\Models\SubscriptionPlan;
use App\Models\ClientSubscription;
use App\Models\SubscriptionHistory;
use App\Models\PaymentRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SubscriptionService
{
    // ============ PLAN MANAGEMENT ============
    
    /**
     * Generate unique subscription ID
     */
    public function generateSubscriptionId()
    {
        $year = date('Y');
        $month = date('m');
        
        $lastSubscription = ClientSubscription::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastSubscription && preg_match('/SUB-' . $year . $month . '-(\d+)/', $lastSubscription->subscription_id, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }
        
        return 'SUB-' . $year . $month . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Create a new subscription plan
     */
    public function createPlan(array $data)
    {
        return DB::transaction(function () use ($data) {
            if (Auth::check()) {
                $data['created_by_user_id'] = Auth::id();
            } elseif (!isset($data['created_by_user_id'])) {
                $firstUser = \App\Models\User::first();
                $data['created_by_user_id'] = $firstUser ? $firstUser->id : 1;
            }
            
            $plan = SubscriptionPlan::create($data);
            return $plan;
        });
    }
    
    /**
     * Update an existing plan
     */
    public function updatePlan($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $plan = SubscriptionPlan::findOrFail($id);
            $plan->update($data);
            return $plan->fresh();
        });
    }
    
    /**
     * Soft delete a plan (only if no active subscriptions)
     */
    public function deletePlan($id)
    {
        return DB::transaction(function () use ($id) {
            $plan = SubscriptionPlan::findOrFail($id);
            
            $activeSubscriptions = $plan->subscriptions()
                ->whereIn('status', ['active', 'trial'])
                ->count();
                
            if ($activeSubscriptions > 0) {
                throw new \Exception('Cannot delete plan with active subscriptions');
            }
            
            $plan->delete();
            return true;
        });
    }
    
    /**
     * Get all active plans
     */
    public function getActivePlans()
    {
        return SubscriptionPlan::where('is_active', true)->get();
    }
    
    /**
     * Toggle plan active status
     */
    public function togglePlanStatus($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $plan->is_active = !$plan->is_active;
        $plan->save();
        return $plan;
    }
    
    // ============ SUBSCRIPTION ASSIGNMENT ============
    
    /**
     * Calculate end date based on billing cycle
     */
    public function calculateEndDate($startDate, $billingCycle, $customDays = null)
    {
        $start = Carbon::parse($startDate);
        
        return match($billingCycle) {
            'monthly' => $start->copy()->addMonth(),
            'quarterly' => $start->copy()->addMonths(3),
            'yearly' => $start->copy()->addYear(),
            'custom' => $start->copy()->addDays($customDays ?? 30),
            default => $start->copy()->addMonth(),
        };
    }
    
    /**
     * Check if client already has active subscription
     */
    public function checkDuplicateActiveSubscription($clientId)
    {
        return ClientSubscription::where('client_id', $clientId)
            ->whereIn('status', ['active', 'trial'])
            ->exists();
    }
    
    /**
     * Assign subscription to client
     */
    public function assignSubscription($clientId, $planId, array $data = [])
    {
        return DB::transaction(function () use ($clientId, $planId, $data) {
            // Check if client already has active subscription
            if ($this->checkDuplicateActiveSubscription($clientId)) {
                throw new \Exception('Client already has an active or trial subscription');
            }
            
            // Get the plan
            $plan = SubscriptionPlan::findOrFail($planId);
            
            // Calculate dates
            $startDate = $data['start_date'] ?? now();
            $endDate = $this->calculateEndDate(
                $startDate, 
                $plan->billing_cycle,
                $data['custom_days'] ?? null
            );
            
            // Generate subscription ID
            $subscriptionId = $this->generateSubscriptionId();
            
            // Prepare subscription data WITH subscription_id
            $subscriptionData = [
                'subscription_id' => $subscriptionId,
                'client_id' => $clientId,
                'plan_id' => $planId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'renewal_date' => $endDate,
                'status' => $data['status'] ?? 'active',
                'payment_method' => $data['payment_method'] ?? null,
                'payment_status' => $data['payment_status'] ?? 'unpaid',
                'discount' => $data['discount'] ?? 0,
                'invoice_reference' => $data['invoice_reference'] ?? null,
                'internal_note' => $data['internal_note'] ?? null,
                'created_by_user_id' => Auth::id() ?? 1,
            ];
            
            // Add trial if applicable
            if (isset($data['is_trial']) && $data['is_trial']) {
                $subscriptionData['status'] = 'trial';
                $subscriptionData['trial_end_date'] = $this->calculateEndDate(
                    $startDate,
                    'custom',
                    $plan->trial_days
                );
            }
            
            // Create subscription
            $subscription = ClientSubscription::create($subscriptionData);
            
            // Log the creation
            $this->logHistory($subscription, 'created', [
                'plan_id' => ['old' => null, 'new' => $planId],
                'status' => ['old' => null, 'new' => $subscription->status],
                'price' => ['old' => null, 'new' => $plan->price]
            ], 'Subscription created');
            
            return $subscription->load('plan', 'client');
        });
    }
    
    // ============ STATUS MANAGEMENT ============
    
    /**
     * Change subscription status
     */
    public function changeStatus($subscriptionId, $newStatus, $reason = null)
    {
        return DB::transaction(function () use ($subscriptionId, $newStatus, $reason) {
            $subscription = ClientSubscription::findOrFail($subscriptionId);
            $oldStatus = $subscription->status;
            
            $subscription->update(['status' => $newStatus]);
            
            $this->logHistory($subscription, 'status_changed', [
                'status' => ['old' => $oldStatus, 'new' => $newStatus]
            ], $reason ?? "Status changed from $oldStatus to $newStatus");
            
            return $subscription->fresh();
        });
    }
    
    /**
     * Cancel subscription
     */
    public function cancelSubscription($subscriptionId, $reason)
    {
        return DB::transaction(function () use ($subscriptionId, $reason) {
            $subscription = ClientSubscription::findOrFail($subscriptionId);
            
            if ($subscription->status === 'cancelled') {
                throw new \Exception('Subscription is already cancelled');
            }
            
            $oldStatus = $subscription->status;
            
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'cancelled_by_user_id' => Auth::id() ?? 1
            ]);
            
            $this->logHistory($subscription, 'cancelled', [
                'status' => ['old' => $oldStatus, 'new' => 'cancelled']
            ], $reason);
            
            return $subscription->fresh();
        });
    }
    
    /**
     * Suspend subscription
     */
    public function suspendSubscription($subscriptionId, $reason)
    {
        return DB::transaction(function () use ($subscriptionId, $reason) {
            $subscription = ClientSubscription::findOrFail($subscriptionId);
            $oldStatus = $subscription->status;
            
            $subscription->update(['status' => 'suspended']);
            
            $this->logHistory($subscription, 'suspended', [
                'status' => ['old' => $oldStatus, 'new' => 'suspended']
            ], $reason);
            
            return $subscription->fresh();
        });
    }
    
    /**
     * Reactivate subscription
     */
    public function reactivateSubscription($subscriptionId)
    {
        return DB::transaction(function () use ($subscriptionId) {
            $subscription = ClientSubscription::findOrFail($subscriptionId);
            $oldStatus = $subscription->status;
            
            $subscription->update(['status' => 'active']);
            
            $this->logHistory($subscription, 'reactivated', [
                'status' => ['old' => $oldStatus, 'new' => 'active']
            ], 'Subscription reactivated');
            
            return $subscription->fresh();
        });
    }
    
    // ============ RENEWAL LOGIC ============
    
    /**
     * Process subscription renewal
     */
    public function processRenewal($subscriptionId)
    {
        return DB::transaction(function () use ($subscriptionId) {
            $oldSubscription = ClientSubscription::findOrFail($subscriptionId);
            
            if (!in_array($oldSubscription->status, ['active', 'past_due'])) {
                throw new \Exception('Only active or past due subscriptions can be renewed');
            }
            
            // Generate new subscription ID
            $newSubscriptionId = $this->generateSubscriptionId();
            
            // Create new subscription
            $newSubscriptionData = [
                'subscription_id' => $newSubscriptionId,
                'client_id' => $oldSubscription->client_id,
                'plan_id' => $oldSubscription->plan_id,
                'start_date' => $oldSubscription->end_date->copy()->addDay(),
                'end_date' => $this->calculateEndDate(
                    $oldSubscription->end_date->copy()->addDay(),
                    $oldSubscription->plan->billing_cycle
                ),
                'renewal_date' => $this->calculateEndDate(
                    $oldSubscription->end_date->copy()->addDay(),
                    $oldSubscription->plan->billing_cycle
                ),
                'status' => 'active',
                'payment_method' => $oldSubscription->payment_method,
                'payment_status' => 'unpaid',
                'discount' => $oldSubscription->discount,
                'created_by_user_id' => Auth::id() ?? 1,
            ];
            
            $newSubscription = ClientSubscription::create($newSubscriptionData);
            
            $this->logHistory($newSubscription, 'renewed', [
                'plan_id' => ['old' => null, 'new' => $oldSubscription->plan_id],
                'price' => ['old' => null, 'new' => $oldSubscription->plan->price]
            ], 'Renewed from subscription ' . $oldSubscription->subscription_id);
            
            return $newSubscription->load('plan', 'client');
        });
    }
    
    /**
     * Get renewals due in X days
     */
    public function getRenewalsDue($days = 30)
    {
        return ClientSubscription::with(['client', 'plan'])
            ->whereIn('status', ['active', 'trial'])
            ->whereDate('renewal_date', '<=', now()->addDays($days))
            ->whereDate('renewal_date', '>=', now())
            ->orderBy('renewal_date')
            ->get();
    }
    
    // ============ PAYMENT TRACKING ============
    
    /**
     * Record a payment
     */
    public function recordPayment($subscriptionId, array $paymentData)
    {
        return DB::transaction(function () use ($subscriptionId, $paymentData) {
            $subscription = ClientSubscription::findOrFail($subscriptionId);
            
            $payment = PaymentRecord::create([
                'transaction_id' => 'PAY-' . strtoupper(uniqid()),
                'subscription_id' => $subscriptionId,
                'client_id' => $subscription->client_id,
                'amount' => $paymentData['amount'],
                'payment_date' => $paymentData['payment_date'] ?? now(),
                'payment_method' => $paymentData['payment_method'],
                'reference_number' => $paymentData['reference_number'] ?? null,
                'status' => $paymentData['status'] ?? 'completed',
                'notes' => $paymentData['notes'] ?? null,
                'recorded_by_user_id' => Auth::id() ?? 1
            ]);
            
            // Update subscription payment status
            $totalPaid = PaymentRecord::where('subscription_id', $subscriptionId)
                ->where('status', 'completed')
                ->sum('amount');
            
            if ($totalPaid >= $subscription->plan->price) {
                $subscription->update(['payment_status' => 'paid']);
            } elseif ($totalPaid > 0) {
                $subscription->update(['payment_status' => 'partial']);
            }
            
            $this->logHistory($subscription, 'payment_received', [], 
                "Payment received: {$paymentData['amount']}");
            
            return $payment;
        });
    }
    
    /**
     * Get payment history for subscription
     */
    public function getPaymentHistory($subscriptionId)
    {
        return PaymentRecord::where('subscription_id', $subscriptionId)
            ->orderBy('payment_date', 'desc')
            ->get();
    }
    
    // ============ HISTORY LOGGING ============
    
    /**
     * Public wrapper for logHistory
     */
    public function addHistory($subscription, $action, $changes = [], $notes = null)
    {
        return $this->logHistory($subscription, $action, $changes, $notes);
    }
    
    /**
     * Log subscription history (protected)
     */
    protected function logHistory($subscription, $action, $changes = [], $notes = null)
    {
        return SubscriptionHistory::create([
            'subscription_id' => $subscription->id,
            'client_id' => $subscription->client_id,
            'action' => $action,
            'old_plan_id' => $changes['plan_id']['old'] ?? $changes['old_plan_id'] ?? null,
            'new_plan_id' => $changes['plan_id']['new'] ?? $changes['new_plan_id'] ?? null,
            'old_status' => $changes['status']['old'] ?? $changes['old_status'] ?? null,
            'new_status' => $changes['status']['new'] ?? $changes['new_status'] ?? null,
            'old_price' => $changes['price']['old'] ?? $changes['old_price'] ?? null,
            'new_price' => $changes['price']['new'] ?? $changes['new_price'] ?? null,
            'old_end_date' => $changes['end_date']['old'] ?? $changes['old_end_date'] ?? null,
            'new_end_date' => $changes['end_date']['new'] ?? $changes['new_end_date'] ?? null,
            'notes' => $notes,
            'performed_by_user_id' => Auth::id() ?? 1,
        ]);
    }
    
    // ============ REPORTING ============
    
    /**
     * Get Monthly Recurring Revenue
     */
    public function getMRR()
    {
        $activeSubscriptions = ClientSubscription::with('plan')
            ->whereIn('status', ['active', 'trial'])
            ->get();
        
        $mrr = 0;
        foreach ($activeSubscriptions as $subscription) {
            $price = $subscription->plan->price;
            $mrr += match($subscription->plan->billing_cycle) {
                'monthly' => $price,
                'quarterly' => $price / 3,
                'yearly' => $price / 12,
                default => $price,
            };
        }
        
        return round($mrr, 2);
    }
    
    /**
     * Get subscription statistics
     */
    public function getSubscriptionStats()
    {
        return [
            'total_active' => ClientSubscription::whereIn('status', ['active', 'trial'])->count(),
            'total_trial' => ClientSubscription::where('status', 'trial')->count(),
            'total_past_due' => ClientSubscription::where('status', 'past_due')->count(),
            'total_cancelled' => ClientSubscription::where('status', 'cancelled')->count(),
            'total_suspended' => ClientSubscription::where('status', 'suspended')->count(),
            'mrr' => $this->getMRR(),
            'renewals_next_30_days' => $this->getRenewalsDue(30)->count(),
        ];
    }
    
    /**
     * Get churn rate for period
     */
    public function getChurnRate($period = 'month')
    {
        $startDate = match($period) {
            'month' => now()->subMonth(),
            'quarter' => now()->subMonths(3),
            'year' => now()->subYear(),
            default => now()->subMonth(),
        };
        
        $cancelled = ClientSubscription::where('status', 'cancelled')
            ->where('updated_at', '>=', $startDate)
            ->count();
        
        $total = ClientSubscription::whereIn('status', ['active', 'trial', 'past_due'])
            ->count();
        
        if ($total === 0) {
            return 0;
        }
        
        return round(($cancelled / $total) * 100, 2);
    }
}