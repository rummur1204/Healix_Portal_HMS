<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\ClientSubscription;
use App\Models\PaymentRecord;
use App\Services\Subscription\SubscriptionService;
use App\Http\Requests\Subscription\RecordPaymentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->middleware('auth');
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
        try {
            $this->checkPermission('record payments');

            Log::info('Payment record attempt:', [
                'subscription_id' => $request->subscription_id,
                'amount' => $request->amount,
                'method' => $request->payment_method,
                'user_id' => auth()->id()
            ]);

            DB::beginTransaction();

            // Get subscription with plan
            $subscription = ClientSubscription::with('plan')
                ->findOrFail($request->subscription_id);

            // Store old status for history
            $oldStatus = $subscription->payment_status;

            // Generate transaction ID
            $transactionId = 'PAY-' . strtoupper(uniqid());

            // Create payment record
            $payment = PaymentRecord::create([
                'transaction_id' => $transactionId,
                'subscription_id' => $subscription->id,
                'client_id' => $subscription->client_id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date ?? now(),
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
                'status' => 'completed',
                'recorded_by_user_id' => auth()->id()
            ]);

            // Calculate total paid
            $totalPaid = PaymentRecord::where('subscription_id', $subscription->id)
                ->where('status', 'completed')
                ->sum('amount');

            // Update subscription payment status
            if ($totalPaid >= $subscription->plan->price) {
                $subscription->payment_status = 'paid';
            } elseif ($totalPaid > 0) {
                $subscription->payment_status = 'partial';
            } else {
                $subscription->payment_status = 'unpaid';
            }
            
            $subscription->save();

            // Log history - using the public wrapper method
            $this->subscriptionService->addHistory(
                $subscription,
                'payment_received',
                [
                    'old_status' => $oldStatus,
                    'new_status' => $subscription->payment_status
                ],
                "Payment received: $" . number_format($request->amount, 2) . " via " . ucfirst($request->payment_method)
            );

            DB::commit();

            Log::info('Payment recorded successfully:', [
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'new_status' => $subscription->payment_status
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment recorded successfully',
                    'payment' => [
                        'id' => $payment->id,
                        'transaction_id' => $payment->transaction_id,
                        'amount' => $payment->amount,
                        'payment_date' => $payment->payment_date,
                        'payment_method' => $payment->payment_method,
                        'status' => $payment->status
                    ],
                    'subscription_status' => $subscription->payment_status,
                    'total_paid' => $totalPaid
                ]);
            }

            return redirect()->route('subscriptions.subscription.show', $request->subscription_id)
                ->with('success', 'Payment recorded successfully. Total paid: $' . number_format($totalPaid, 2));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Payment recording - Subscription not found:', [
                'subscription_id' => $request->subscription_id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Subscription not found'
                ], 404);
            }

            return redirect()->back()
                ->with('error', 'Subscription not found')
                ->withInput();

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('Payment recording - Validation error:', [
                'errors' => $e->errors()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment recording error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->except(['_token'])
            ]);

            $errorMessage = config('app.debug') ? $e->getMessage() : 'Failed to record payment. Please try again.';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $errorMessage
                ], 500);
            }

            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
        }
    }

    /**
     * Get payment history for subscription
     */
    public function history($subscriptionId)
    {
        try {
            $this->checkPermission('view payment history');

            $subscription = ClientSubscription::with('client', 'plan')
                ->findOrFail($subscriptionId);
            
            $payments = PaymentRecord::where('subscription_id', $subscriptionId)
                ->with('recordedBy')
                ->orderBy('payment_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'transaction_id' => $payment->transaction_id,
                        'amount' => $payment->amount,
                        'payment_date' => $payment->payment_date,
                        'payment_method' => $payment->payment_method,
                        'reference_number' => $payment->reference_number,
                        'status' => $payment->status,
                        'notes' => $payment->notes,
                        'recorded_by' => $payment->recordedBy ? [
                            'id' => $payment->recordedBy->id,
                            'name' => $payment->recordedBy->name
                        ] : null,
                        'created_at' => $payment->created_at
                    ];
                });

            // Calculate totals
            $totalPaid = $payments->where('status', 'completed')->sum('amount');
            $pendingAmount = $payments->where('status', 'pending')->sum('amount');

            return response()->json([
                'success' => true,
                'subscription' => [
                    'id' => $subscription->id,
                    'subscription_id' => $subscription->subscription_id,
                    'client_name' => $subscription->client?->organization_name,
                    'plan_name' => $subscription->plan?->plan_name,
                    'plan_price' => $subscription->plan?->price,
                    'payment_status' => $subscription->payment_status
                ],
                'payments' => $payments,
                'summary' => [
                    'total_paid' => $totalPaid,
                    'pending_amount' => $pendingAmount,
                    'remaining' => max(0, ($subscription->plan?->price ?? 0) - $totalPaid),
                    'payment_count' => $payments->count()
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Payment history - Subscription not found:', [
                'subscription_id' => $subscriptionId
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Subscription not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Payment history error:', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch payment history'
            ], 500);
        }
    }

    /**
     * Void a payment (mark as refunded)
     */
    public function void($id, Request $request)
    {
        try {
            $this->checkPermission('void payments');

            Log::info('Payment void attempt:', ['payment_id' => $id, 'user_id' => auth()->id()]);

            DB::beginTransaction();

            $payment = PaymentRecord::with('subscription.plan')
                ->findOrFail($id);

            if ($payment->status === 'refunded') {
                return response()->json([
                    'success' => false,
                    'error' => 'Payment is already voided'
                ], 400);
            }

            $payment->status = 'refunded';
            $payment->notes = ($payment->notes ? $payment->notes . "\n" : '') . 
                "Voided on " . now()->format('Y-m-d H:i:s') . " by " . auth()->user()->name;
            $payment->save();

            // Recalculate subscription payment status
            $subscription = $payment->subscription;
            $oldStatus = $subscription->payment_status;
            
            $totalPaid = PaymentRecord::where('subscription_id', $subscription->id)
                ->where('status', 'completed')
                ->sum('amount');

            if ($totalPaid >= $subscription->plan->price) {
                $subscription->payment_status = 'paid';
            } elseif ($totalPaid > 0) {
                $subscription->payment_status = 'partial';
            } else {
                $subscription->payment_status = 'unpaid';
            }
            
            $subscription->save();

            // Log history - using the public wrapper method
            $this->subscriptionService->addHistory(
                $subscription,
                'payment_voided',
                [
                    'old_status' => $oldStatus,
                    'new_status' => $subscription->payment_status
                ],
                "Payment {$payment->transaction_id} voided (Amount: $" . number_format($payment->amount, 2) . ")"
            );

            DB::commit();

            Log::info('Payment voided successfully:', ['payment_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Payment voided successfully',
                'payment_status' => $payment->status,
                'subscription_status' => $subscription->payment_status
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Payment not found'
            ], 404);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment void error:', [
                'payment_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to void payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment summary for subscription
     */
    public function summary($subscriptionId)
    {
        try {
            $this->checkPermission('view payment history');

            $subscription = ClientSubscription::with('plan')->findOrFail($subscriptionId);
            
            $payments = PaymentRecord::where('subscription_id', $subscriptionId)
                ->where('status', 'completed')
                ->get();

            $totalPaid = $payments->sum('amount');
            $planPrice = $subscription->plan?->price ?? 0;

            return response()->json([
                'success' => true,
                'summary' => [
                    'total_paid' => $totalPaid,
                    'plan_price' => $planPrice,
                    'remaining' => max(0, $planPrice - $totalPaid),
                    'payment_count' => $payments->count(),
                    'is_fully_paid' => $totalPaid >= $planPrice,
                    'payment_status' => $subscription->payment_status
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Payment summary error:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch payment summary'
            ], 500);
        }
    }
}