<?php

namespace App\Console\Commands;

use App\Models\ClientSubscription;
use App\Services\Subscription\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckSubscriptions extends Command
{
    protected $signature = 'subscriptions:check';
    protected $description = 'Check subscription statuses and update accordingly';

    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        parent::__construct();
        $this->subscriptionService = $subscriptionService;
    }

    public function handle()
    {
        $this->info('Checking subscriptions...');

        // Check for expired trials
        $expiredTrials = ClientSubscription::where('status', 'trial')
            ->where('trial_end_date', '<', now())
            ->get();

        foreach ($expiredTrials as $subscription) {
            $this->subscriptionService->changeStatus(
                $subscription->id,
                'active',
                'Trial period ended'
            );
            $this->info("Trial ended for subscription: {$subscription->subscription_id}");
        }

        // Check for past due subscriptions (30 days after end date -> suspended)
        $pastDueToSuspend = ClientSubscription::where('status', 'past_due')
            ->where('end_date', '<', now()->subDays(30))
            ->get();

        foreach ($pastDueToSuspend as $subscription) {
            $this->subscriptionService->changeStatus(
                $subscription->id,
                'suspended',
                'Suspended after 30 days past due'
            );
            $this->info("Suspended subscription: {$subscription->subscription_id}");
        }

        // Check for suspended to cancel (60 days after end date -> cancelled)
        $suspendedToCancel = ClientSubscription::where('status', 'suspended')
            ->where('end_date', '<', now()->subDays(60))
            ->get();

        foreach ($suspendedToCancel as $subscription) {
            $this->subscriptionService->changeStatus(
                $subscription->id,
                'cancelled',
                'Cancelled after 60 days suspended'
            );
            $this->info("Cancelled subscription: {$subscription->subscription_id}");
        }

        $this->info('Subscription check completed!');
        
        return Command::SUCCESS;
    }
}