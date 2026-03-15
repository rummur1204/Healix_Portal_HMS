<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\ClientSubscription;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RenewalController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Get renewals for the main index page
     */
    public function getRenewalsForIndex($days = 30)
    {
        try {
            return $this->subscriptionService->getRenewalsDue($days);
        } catch (\Exception $e) {
            Log::error('Error in getRenewalsForIndex: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Display renewals due
     */
    public function index(Request $request)
    {
        try {
            $days = $request->get('days', 30);
            $renewals = $this->subscriptionService->getRenewalsDue($days);

            if ($request->header('X-Inertia')) {
                return Inertia::render('Subscriptions/Renewals/Index', [
                    'renewals' => $renewals,
                    'days' => $days
                ]);
            }

            return response()->json([
                'data' => $renewals
            ]);
        } catch (\Exception $e) {
            Log::error('Error in index: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get renewals due in specified days - FIXED to return JSON for API calls
     */
    public function due(Request $request, $days = 30)
    {
        try {
            // Get the days parameter from the URL if provided
            $days = (int) $days;
            
            Log::info('Fetching renewals due in next ' . $days . ' days');
            
            $renewals = $this->subscriptionService->getRenewalsDue($days);
            
            Log::info('Found ' . $renewals->count() . ' renewals due');

            // Return JSON response for API calls from the frontend
            return response()->json([
                'renewals' => $renewals
            ]);
        } catch (\Exception $e) {
            Log::error('Error in due: ' . $e->getMessage());
            return response()->json([
                'renewals' => [],
                'error' => 'Failed to load renewals: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process a renewal
     */
    public function process(Request $request, $id)
    {
        try {
            Log::info('Processing renewal for subscription ID: ' . $id);
            
            $subscription = $this->subscriptionService->processRenewal($id);

            Log::info('Renewal processed successfully for subscription ID: ' . $id);

            if ($request->header('X-Inertia')) {
                return redirect()->back()
                    ->with('success', 'Renewal processed successfully.');
            }

            return response()->json([
                'success' => true,
                'message' => 'Renewal processed successfully',
                'subscription' => $subscription
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing renewal: ' . $e->getMessage());
            
            if ($request->header('X-Inertia')) {
                return redirect()->back()
                    ->with('error', 'Failed to process renewal: ' . $e->getMessage());
            }

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}