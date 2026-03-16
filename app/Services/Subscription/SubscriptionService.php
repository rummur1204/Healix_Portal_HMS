<?php

namespace App\Services\Subscription;

use App\Models\ClientSubscription;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionHistory;
use App\Models\Client;
use App\Models\RenewalReminder;
use App\Models\DiscountApproval;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SubscriptionService
{
    /**
     * Discount approval thresholds
     */
    const DISCOUNT_APPROVAL_THRESHOLD = 100; // Amount in dollars
    const DISCOUNT_PERCENTAGE_THRESHOLD = 20; // Percentage threshold

    /**
     * Get renewals due within a certain number of days
     */
    public function getRenewalsDue($days = 30)
    {
        try {
            Log::info('Getting renewals due', ['days' => $days]);
            
            // Build the query
            $query = DB::table('renewal_reminders')
                ->join('client_subscriptions', 'renewal_reminders.subscription_id', '=', 'client_subscriptions.id')
                ->join('clients', 'client_subscriptions.client_id', '=', 'clients.id')
                ->join('subscription_plans', 'client_subscriptions.plan_id', '=', 'subscription_plans.id')
                ->where('renewal_reminders.is_sent', false)
                ->whereIn('client_subscriptions.status', ['active', 'trial']);
            
            // Apply days filter - if days is 999, show all
            if ($days < 999) {
                $query->where('renewal_reminders.reminder_days', '<=', $days);
            }
            
            $renewals = $query->select(
                    'client_subscriptions.id',
                    'client_subscriptions.subscription_id',
                    'client_subscriptions.start_date',
                    'client_subscriptions.end_date',
                    'client_subscriptions.status',
                    'client_subscriptions.payment_method',
                    'client_subscriptions.payment_status',
                    'clients.id as client_id',
                    'clients.organization_name',
                    'clients.primary_contact_email',
                    'clients.primary_contact_phone',
                    'subscription_plans.id as plan_id',
                    'subscription_plans.plan_name',
                    'subscription_plans.price',
                    'subscription_plans.billing_cycle',
                    'renewal_reminders.reminder_days',
                    'renewal_reminders.is_sent',
                    'renewal_reminders.sent_at',
                    'client_subscriptions.end_date as renewal_date'
                )
                ->orderBy('renewal_reminders.reminder_days', 'asc')
                ->get();
            
            // Transform the collection to match the expected format
            $renewals = $renewals->map(function ($item) {
                return (object) [
                    'id' => $item->id,
                    'subscription_id' => $item->subscription_id,
                    'renewal_date' => $item->end_date,
                    'reminder_days' => $item->reminder_days,
                    'is_sent' => $item->is_sent,
                    'sent_at' => $item->sent_at,
                    'client' => (object) [
                        'id' => $item->client_id,
                        'organization_name' => $item->organization_name,
                        'primary_contact_email' => $item->primary_contact_email,
                        'primary_contact_phone' => $item->primary_contact_phone
                    ],
                    'plan' => (object) [
                        'id' => $item->plan_id,
                        'plan_name' => $item->plan_name,
                        'price' => $item->price,
                        'billing_cycle' => $item->billing_cycle
                    ]
                ];
            });
            
            Log::info('Found ' . $renewals->count() . ' renewals due');
            
            return $renewals;
        } catch (\Exception $e) {
            Log::error('Error in getRenewalsDue: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Process a renewal
     */
    public function processRenewal($subscriptionId)
    {
        try {
            DB::beginTransaction();

            Log::info('Processing renewal', ['subscription_id' => $subscriptionId]);

            $subscription = ClientSubscription::with(['plan', 'client'])->findOrFail($subscriptionId);
            
            // Store old end date for history
            $oldEndDate = $subscription->end_date;
            
            // Calculate new end date based on billing cycle
            $newEndDate = $this->calculateNewEndDate($subscription);
            
            // Update subscription
            $subscription->update([
                'start_date' => Carbon::now(),
                'end_date' => $newEndDate,
                'status' => 'active',
                'payment_status' => 'paid',
                'renewed_at' => Carbon::now()
            ]);

            // Mark the reminder as sent
            RenewalReminder::where('subscription_id', $subscriptionId)
                ->update(['is_sent' => true, 'sent_at' => Carbon::now()]);

            // Add history
            $this->addHistory(
                $subscription,
                'renewed',
                [
                    'old_end_date' => $oldEndDate,
                    'new_end_date' => $newEndDate
                ],
                'Subscription renewed automatically'
            );

            DB::commit();

            Log::info('Renewal processed successfully', ['subscription_id' => $subscriptionId]);

            return $subscription->fresh(['client', 'plan']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing renewal: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate new end date based on billing cycle
     */
    private function calculateNewEndDate($subscription)
    {
        $plan = $subscription->plan;
        $now = Carbon::now();
        
        switch ($plan->billing_cycle) {
            case 'monthly':
                return $now->copy()->addMonth();
            case 'quarterly':
                return $now->copy()->addMonths(3);
            case 'yearly':
                return $now->copy()->addYear();
            case 'custom':
                return $now->copy()->addMonth();
            default:
                return $now->copy()->addMonth();
        }
    }

    /**
     * Create a new plan
     */
    public function createPlan(array $data)
    {
        try {
            if (isset($data['is_active'])) {
                $data['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
            } else {
                $data['is_active'] = true;
            }
            
            if (isset($data['price'])) {
                $data['price'] = (string) $data['price'];
            }
            
            if (isset($data['trial_days']) && empty($data['trial_days'])) {
                $data['trial_days'] = 0;
            }
            
            if (isset($data['max_users']) && empty($data['max_users'])) {
                $data['max_users'] = null;
            }
            
            if (isset($data['max_branches']) && empty($data['max_branches'])) {
                $data['max_branches'] = null;
            }
            
            $data['created_by_user_id'] = auth()->id();
            
            unset($data['active_subscriptions_count']);
            
            return SubscriptionPlan::create($data);
        } catch (\Exception $e) {
            Log::error('Error creating plan: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing plan
     */
    public function updatePlan($id, array $data)
    {
        try {
            $plan = SubscriptionPlan::findOrFail($id);
            
            if (isset($data['is_active'])) {
                $data['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
            }
            
            if (isset($data['price'])) {
                $data['price'] = (string) $data['price'];
            }
            
            if (isset($data['trial_days']) && empty($data['trial_days'])) {
                $data['trial_days'] = 0;
            }
            
            if (isset($data['max_users']) && empty($data['max_users'])) {
                $data['max_users'] = null;
            }
            
            if (isset($data['max_branches']) && empty($data['max_branches'])) {
                $data['max_branches'] = null;
            }
            
            unset($data['active_subscriptions_count']);
            unset($data['created_by_user_id']);
            
            $plan->update($data);
            return $plan;
        } catch (\Exception $e) {
            Log::error('Error updating plan: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a plan
     */
    public function deletePlan($id)
    {
        try {
            $plan = SubscriptionPlan::findOrFail($id);
            
            if ($plan->subscriptions()->whereIn('status', ['active', 'trial'])->exists()) {
                throw new \Exception('Cannot delete plan with active subscriptions');
            }
            
            $plan->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Error deleting plan: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Assign subscription to client - UPDATED with discount approval logic
     */
    public function assignSubscription($clientId, $planId, array $data = [])
    {
        try {
            Log::info('========== STARTING SUBSCRIPTION ASSIGNMENT ==========');
            Log::info('Input data:', [
                'client_id' => $clientId,
                'plan_id' => $planId,
                'additional_data' => $data,
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name ?? 'System'
            ]);

            DB::beginTransaction();

            // Find client
            $client = Client::find($clientId);
            if (!$client) {
                Log::error('CLIENT NOT FOUND', ['client_id' => $clientId]);
                throw new \Exception("Client with ID {$clientId} not found");
            }
            Log::info('Client found:', ['id' => $client->id, 'name' => $client->organization_name]);

            // Find plan
            $plan = SubscriptionPlan::find($planId);
            if (!$plan) {
                Log::error('PLAN NOT FOUND', ['plan_id' => $planId]);
                throw new \Exception("Plan with ID {$planId} not found");
            }
            Log::info('Plan found:', ['id' => $plan->id, 'name' => $plan->plan_name, 'price' => $plan->price]);

            // Check if plan is active
            if (!$plan->is_active) {
                Log::error('PLAN IS INACTIVE', ['plan_id' => $planId]);
                throw new \Exception('Cannot assign subscription to an inactive plan');
            }

            // Generate unique subscription ID
            $subscriptionId = 'SUB-' . strtoupper(Str::random(8));
            Log::info('Generated subscription ID:', ['subscription_id' => $subscriptionId]);
            
            // Calculate dates
            $startDate = isset($data['start_date']) && !empty($data['start_date'])
                ? Carbon::parse($data['start_date']) 
                : Carbon::now();
            
            $endDate = $this->calculateEndDate($startDate, $plan->billing_cycle);
            
            Log::info('Calculated dates:', [
                'start_date' => $startDate->toDateTimeString(),
                'end_date' => $endDate->toDateTimeString(),
                'billing_cycle' => $plan->billing_cycle
            ]);

            // Determine initial status
            $initialStatus = 'active';
            $trialEndsAt = null;
            
            if ($plan->trial_days > 0) {
                $initialStatus = 'trial';
                $trialEndsAt = Carbon::now()->addDays($plan->trial_days);
                Log::info('Trial period applied', [
                    'trial_days' => $plan->trial_days,
                    'trial_ends_at' => $trialEndsAt->toDateTimeString()
                ]);
            }

            // Create subscription
            $subscription = ClientSubscription::create([
                'subscription_id' => $subscriptionId,
                'client_id' => $clientId,
                'plan_id' => $planId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'trial_end_date' => $trialEndsAt,
                'status' => $data['status'] ?? $initialStatus,
                'payment_method' => $data['payment_method'] ?? null,
                'payment_status' => $data['payment_status'] ?? 'unpaid',
                'discount' => 0,
                'discount_status' => 'none',
                'created_by_user_id' => auth()->id(),
            ]);

            Log::info('Subscription created with ID: ' . $subscription->id);

            // Handle discount if provided
            if (isset($data['discount']) && $data['discount'] > 0) {
                $requiresApproval = $this->requiresDiscountApproval($data['discount'], $plan->price);
                
                if ($requiresApproval) {
                    // Create approval request instead of applying discount directly
                    $this->requestDiscountApproval($subscription->id, $data['discount'], $data['discount_reason'] ?? null);
                    
                    // Update subscription with pending status
                    $subscription->update([
                        'discount' => 0,
                        'discount_status' => 'pending_approval'
                    ]);
                    
                    Log::info('Discount requires approval', [
                        'discount' => $data['discount'],
                        'plan_price' => $plan->price
                    ]);
                } else {
                    $subscription->update([
                        'discount' => $data['discount'],
                        'discount_status' => 'approved'
                    ]);
                    
                    Log::info('Discount applied automatically', ['discount' => $data['discount']]);
                }
            }

            // Load relationships
            $subscription->load(['client', 'plan']);
            
            Log::info('Relationships loaded', [
                'client_name' => $subscription->client->organization_name ?? 'N/A',
                'plan_name' => $subscription->plan->plan_name ?? 'N/A'
            ]);

            // Add history
            $this->addHistory(
                $subscription,
                'assigned',
                [
                    'plan_name' => $plan->plan_name,
                    'client_name' => $client->organization_name,
                    'discount' => $data['discount'] ?? 0,
                    'discount_status' => $subscription->discount_status
                ],
                'Subscription assigned to client by ' . (auth()->user()->name ?? 'System')
            );

            DB::commit();
            
            Log::info('========== SUBSCRIPTION ASSIGNMENT COMPLETED SUCCESSFULLY ==========');

            return $subscription;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('========== SUBSCRIPTION ASSIGNMENT FAILED ==========');
            Log::error('Error message: ' . $e->getMessage());
            Log::error('Error file: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Error trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Change subscription plan (upgrade/downgrade)
     */
    public function changePlan($subscriptionId, $newPlanId, $effectiveDate = 'immediate')
    {
        try {
            DB::beginTransaction();

            $subscription = ClientSubscription::with(['plan', 'client'])->findOrFail($subscriptionId);
            $oldPlan = $subscription->plan;
            $newPlan = SubscriptionPlan::findOrFail($newPlanId);
            
            $oldEndDate = $subscription->end_date;
            $action = $newPlan->price > $oldPlan->price ? 'upgraded' : 'downgraded';
            
            // Store old values for history
            $oldPlanId = $subscription->plan_id;
            $oldPrice = $subscription->amount ?? $oldPlan->price;
            
            // Update subscription with new plan
            if ($effectiveDate === 'immediate') {
                // Calculate prorated amount if needed
                $subscription->update([
                    'plan_id' => $newPlanId,
                    'amount' => $newPlan->price,
                ]);
            } else {
                // Schedule for next renewal
                $subscription->update([
                    'plan_id' => $newPlanId,
                    'amount' => $newPlan->price,
                ]);
            }

            // Add detailed history
            $this->addHistory(
                $subscription,
                $action,
                [
                    'old_plan_id' => $oldPlanId,
                    'new_plan_id' => $newPlanId,
                    'old_price' => $oldPrice,
                    'new_price' => $newPlan->price,
                    'old_end_date' => $oldEndDate,
                    'change_type' => $action,
                    'effective_date' => $effectiveDate
                ],
                "Plan {$action} from {$oldPlan->plan_name} to {$newPlan->plan_name}"
            );

            DB::commit();

            return $subscription->fresh(['client', 'plan']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error changing plan: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if discount requires approval
     */
    public function requiresDiscountApproval($discountAmount, $originalPrice)
    {
        $discountPercentage = ($discountAmount / $originalPrice) * 100;
        
        return $discountAmount > self::DISCOUNT_APPROVAL_THRESHOLD || 
               $discountPercentage > self::DISCOUNT_PERCENTAGE_THRESHOLD;
    }

    /**
     * Request discount approval
     */
    public function requestDiscountApproval($subscriptionId, $discountAmount, $reason = null)
    {
        try {
            DB::beginTransaction();
            
            $subscription = ClientSubscription::with('plan')->findOrFail($subscriptionId);
            $originalPrice = $subscription->plan->price;
            $discountPercentage = ($discountAmount / $originalPrice) * 100;
            
            // Create approval request
            $approval = DiscountApproval::create([
                'subscription_id' => $subscriptionId,
                'discount_amount' => $discountAmount,
                'original_price' => $originalPrice,
                'discount_percentage' => $discountPercentage,
                'reason' => $reason,
                'status' => 'pending',
                'requested_by_user_id' => auth()->id()
            ]);
            
            // Update subscription with pending discount
            $subscription->update([
                'discount' => 0,
                'discount_status' => 'pending_approval'
            ]);
            
            // Add history
            $this->addHistory(
                $subscription,
                'discount_requested',
                [
                    'discount_amount' => $discountAmount,
                    'discount_percentage' => $discountPercentage
                ],
                "Discount of $$discountAmount (" . number_format($discountPercentage, 2) . "%) requested for approval"
            );
            
            DB::commit();
            
            Log::info('Discount approval requested', [
                'subscription_id' => $subscriptionId,
                'discount' => $discountAmount,
                'approval_id' => $approval->id
            ]);
            
            return $approval;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error requesting discount approval: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process discount approval (approve/reject) - FIXED VERSION
     */
    public function processDiscountApproval($approvalId, $status, $rejectionReason = null)
    {
        try {
            DB::beginTransaction();
            
            $approval = DiscountApproval::with('subscription')->findOrFail($approvalId);
            
            if ($approval->status !== 'pending') {
                throw new \Exception('This discount request has already been processed');
            }
            
            // Update approval record
            $approval->update([
                'status' => $status,
                'approved_by_user_id' => auth()->id(),
                'approved_at' => $status === 'approved' ? now() : null,
                'rejection_reason' => $rejectionReason
            ]);
            
            // Get the subscription from the relationship
            $subscription = $approval->subscription;
            
            if (!$subscription) {
                throw new \Exception('Subscription not found for this approval');
            }
            
            if ($status === 'approved') {
                // Apply the discount to the subscription
                $subscription->update([
                    'discount' => $approval->discount_amount,
                    'discount_status' => 'approved'
                ]);
                
                Log::info('Discount approved and applied to subscription', [
                    'subscription_id' => $subscription->id,
                    'discount' => $approval->discount_amount,
                    'old_discount' => $subscription->getOriginal('discount')
                ]);
                
                $this->addHistory(
                    $subscription,
                    'discount_approved',
                    ['discount_amount' => $approval->discount_amount],
                    "Discount of $$approval->discount_amount approved and applied"
                );
            } else {
                // Reject the discount
                $subscription->update([
                    'discount_status' => 'rejected'
                    // Keep existing discount if any
                ]);
                
                $this->addHistory(
                    $subscription,
                    'discount_rejected',
                    ['reason' => $rejectionReason],
                    "Discount request rejected: $rejectionReason"
                );
            }
            
            DB::commit();
            
            Log::info('Discount approval processed successfully', [
                'approval_id' => $approvalId,
                'status' => $status,
                'subscription_id' => $subscription->id,
                'new_discount' => $subscription->fresh()->discount,
                'new_status' => $subscription->fresh()->discount_status
            ]);
            
            return $approval->fresh(['subscription']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing discount approval: ' . $e->getMessage(), [
                'approval_id' => $approvalId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription($id, $reason = null)
    {
        try {
            DB::beginTransaction();

            $subscription = ClientSubscription::with(['client', 'plan'])->findOrFail($id);
            
            if ($subscription->status === 'cancelled') {
                throw new \Exception('Subscription is already cancelled');
            }
            
            $oldStatus = $subscription->status;
            
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => Carbon::now(),
                'cancellation_reason' => $reason,
                'cancelled_by_user_id' => auth()->id(),
            ]);

            $this->addHistory(
                $subscription,
                'cancelled',
                ['old_status' => $oldStatus, 'new_status' => 'cancelled'],
                $reason ?? 'Subscription cancelled by ' . (auth()->user()->name ?? 'System')
            );

            DB::commit();

            return $subscription->fresh(['client', 'plan']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling subscription: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Suspend subscription
     */
    public function suspendSubscription($id, $reason = null)
    {
        try {
            DB::beginTransaction();

            $subscription = ClientSubscription::with(['client', 'plan'])->findOrFail($id);
            
            if ($subscription->status === 'suspended') {
                throw new \Exception('Subscription is already suspended');
            }
            
            $oldStatus = $subscription->status;
            
            $subscription->update([
                'status' => 'suspended',
                'suspended_at' => Carbon::now(),
                'suspension_reason' => $reason
            ]);

            $this->addHistory(
                $subscription,
                'suspended',
                ['old_status' => $oldStatus, 'new_status' => 'suspended'],
                $reason ?? 'Subscription suspended by ' . (auth()->user()->name ?? 'System')
            );

            DB::commit();

            return $subscription->fresh(['client', 'plan']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error suspending subscription: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reactivate subscription
     */
    public function reactivateSubscription($id)
    {
        try {
            DB::beginTransaction();

            $subscription = ClientSubscription::with(['client', 'plan'])->findOrFail($id);
            
            if ($subscription->status === 'active') {
                throw new \Exception('Subscription is already active');
            }
            
            $oldStatus = $subscription->status;
            
            $subscription->update([
                'status' => 'active',
                'reactivated_at' => Carbon::now()
            ]);

            $this->addHistory(
                $subscription,
                'reactivated',
                ['old_status' => $oldStatus, 'new_status' => 'active'],
                'Subscription reactivated by ' . (auth()->user()->name ?? 'System')
            );

            DB::commit();

            return $subscription->fresh(['client', 'plan']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error reactivating subscription: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add history entry - UPDATED with more fields
     */
    public function addHistory($subscription, $action, $changes = [], $notes = null)
    {
        try {
            $historyData = [
                'subscription_id' => $subscription->id,
                'client_id' => $subscription->client_id,
                'action' => $action,
                'notes' => $notes,
                'performed_by_user_id' => auth()->id() ?? 1
            ];

            // Handle plan changes
            if (isset($changes['old_plan_id'])) {
                $historyData['old_plan_id'] = $changes['old_plan_id'];
            } else {
                $historyData['old_plan_id'] = $subscription->plan_id;
            }

            if (isset($changes['new_plan_id'])) {
                $historyData['new_plan_id'] = $changes['new_plan_id'];
            }

            // Handle status changes
            if (isset($changes['old_status'])) {
                $historyData['old_status'] = $changes['old_status'];
            } else {
                $historyData['old_status'] = $subscription->status;
            }

            if (isset($changes['new_status'])) {
                $historyData['new_status'] = $changes['new_status'];
            }

            // Handle price changes
            if (isset($changes['old_price'])) {
                $historyData['old_price'] = $changes['old_price'];
            } else {
                $historyData['old_price'] = $subscription->plan->price ?? null;
            }

            if (isset($changes['new_price'])) {
                $historyData['new_price'] = $changes['new_price'];
            }

            // Handle date changes
            if (isset($changes['old_end_date'])) {
                $historyData['old_end_date'] = $changes['old_end_date'];
            } else {
                $historyData['old_end_date'] = $subscription->end_date;
            }

            if (isset($changes['new_end_date'])) {
                $historyData['new_end_date'] = $changes['new_end_date'];
            }

            // Handle discount changes
            if (isset($changes['discount'])) {
                $historyData['notes'] = ($historyData['notes'] ?? '') . " | Discount: $" . $changes['discount'];
            }

            if (isset($changes['discount_status'])) {
                $historyData['notes'] = ($historyData['notes'] ?? '') . " | Discount Status: " . $changes['discount_status'];
            }

            if (isset($changes['discount_amount'])) {
                $historyData['notes'] = ($historyData['notes'] ?? '') . " | Discount Amount: $" . $changes['discount_amount'];
            }

            if (isset($changes['discount_percentage'])) {
                $historyData['notes'] = ($historyData['notes'] ?? '') . " | Discount Percentage: " . number_format($changes['discount_percentage'], 2) . "%";
            }

            // Handle change type (upgrade/downgrade)
            if (isset($changes['change_type'])) {
                $historyData['notes'] = ($historyData['notes'] ?? '') . " | Change Type: " . $changes['change_type'];
            }

            if (isset($changes['effective_date'])) {
                $historyData['notes'] = ($historyData['notes'] ?? '') . " | Effective: " . $changes['effective_date'];
            }

            return SubscriptionHistory::create($historyData);
        } catch (\Exception $e) {
            Log::error('Error adding subscription history: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate end date based on billing cycle
     */
    private function calculateEndDate($startDate, $billingCycle)
    {
        $start = Carbon::parse($startDate);
        
        switch ($billingCycle) {
            case 'monthly':
                return $start->copy()->addMonth();
            case 'quarterly':
                return $start->copy()->addMonths(3);
            case 'yearly':
                return $start->copy()->addYear();
            case 'custom':
                return $start->copy()->addMonth();
            default:
                return $start->copy()->addMonth();
        }
    }
}