<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\DiscountApproval;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class DiscountApprovalController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function pending()
    {
        try {
            $pendingApprovals = DiscountApproval::with(['subscription.client', 'subscription.plan', 'requestedBy'])
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();

            return Inertia::render('Subscriptions/DiscountApprovals', [
                'approvals' => $pendingApprovals
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading pending approvals: ' . $e->getMessage());
            return Inertia::render('Subscriptions/DiscountApprovals', [
                'approvals' => [],
                'error' => 'Failed to load pending approvals'
            ]);
        }
    }

    public function approve($id)
    {
        try {
            $this->subscriptionService->processDiscountApproval($id, 'approved');
            return redirect()->back()->with('success', 'Discount approved successfully.');
        } catch (\Exception $e) {
            Log::error('Error approving discount: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        try {
            $validated = $request->validate(['reason' => 'required|string']);
            $this->subscriptionService->processDiscountApproval($id, 'rejected', $validated['reason']);
            return redirect()->back()->with('success', 'Discount rejected.');
        } catch (\Exception $e) {
            Log::error('Error rejecting discount: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}