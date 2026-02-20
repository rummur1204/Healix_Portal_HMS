<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\ClientSubscription;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RenewalsExport;

class SubscriptionReportController extends Controller
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
     * Show renewals report
     */
    public function renewals(Request $request)
    {
        $this->checkPermission('view reports');

        $renewals = $this->subscriptionService->getRenewalsDue($request->days ?? 30);

        return Inertia::render('Subscriptions/Reports/Renewals', [
            'renewals' => $renewals,
            'days' => $request->days ?? 30,
        ]);
    }

    /**
     * Show subscription summary
     */
    public function summary(Request $request)
    {
        $this->checkPermission('view reports');

        $stats = [
            'total_plans' => SubscriptionPlan::count(),
            'active_subscriptions' => ClientSubscription::whereIn('status', ['active', 'trial'])->count(),
            'monthly_revenue' => $this->subscriptionService->getMRR(),
            'renewals_due' => $this->subscriptionService->getRenewalsDue(30)->count(),
        ];

        if ($request->wantsJson()) {
            return response()->json(['stats' => $stats]);
        }

        return Inertia::render('Subscriptions/Reports/Summary', [
            'stats' => $stats,
        ]);
    }

    /**
     * Show MRR report
     */
    public function mrr(Request $request)
    {
        $this->checkPermission('view reports');

        $mrr = $this->subscriptionService->getMRR();
        
        // Get MRR by plan
        $plans = SubscriptionPlan::with(['subscriptions' => function ($query) {
            $query->whereIn('status', ['active', 'trial']);
        }])->get();

        $mrrByPlan = $plans->map(function ($plan) {
            $planMrr = 0;
            foreach ($plan->subscriptions as $sub) {
                $planMrr += match($plan->billing_cycle) {
                    'monthly' => $plan->price,
                    'quarterly' => $plan->price / 3,
                    'yearly' => $plan->price / 12,
                    default => $plan->price,
                };
            }
            return [
                'plan_name' => $plan->plan_name,
                'mrr' => round($planMrr, 2),
                'count' => $plan->subscriptions->count(),
            ];
        });

        if ($request->wantsJson()) {
            return response()->json([
                'total_mrr' => $mrr,
                'mrr_by_plan' => $mrrByPlan,
            ]);
        }

        return Inertia::render('Subscriptions/Reports/MRR', [
            'total_mrr' => $mrr,
            'mrr_by_plan' => $mrrByPlan,
        ]);
    }

    /**
     * Show churn report
     */
    public function churn(Request $request)
    {
        $this->checkPermission('view reports');

        $data = [
            'monthly_churn' => $this->subscriptionService->getChurnRate('month'),
            'quarterly_churn' => $this->subscriptionService->getChurnRate('quarter'),
            'yearly_churn' => $this->subscriptionService->getChurnRate('year'),
        ];

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        return Inertia::render('Subscriptions/Reports/Churn', $data);
    }

    /**
     * Export renewals to Excel
     */
    public function exportRenewals(Request $request)
    {
        $this->checkPermission('export subscription reports');

        $days = $request->days ?? 30;
        $renewals = $this->subscriptionService->getRenewalsDue($days);

        return Excel::download(new RenewalsExport($renewals), "renewals-{$days}days.xlsx");
    }
}