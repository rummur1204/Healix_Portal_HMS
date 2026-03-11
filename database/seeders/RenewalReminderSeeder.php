<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RenewalReminderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing reminders first
        DB::table('renewal_reminders')->truncate();
        
        // Get all active subscriptions
        $subscriptions = DB::table('client_subscriptions')
            ->whereIn('status', ['active', 'trial'])
            ->get();

        foreach ($subscriptions as $subscription) {
            // Calculate days until renewal properly
            $endDate = Carbon::parse($subscription->end_date);
            $today = Carbon::today();
            
            // Only include future renewals
            if ($endDate->greaterThan($today)) {
                $daysUntilRenewal = $today->diffInDays($endDate);
                
                DB::table('renewal_reminders')->insert([
                    'subscription_id' => $subscription->id,
                    'reminder_days' => $daysUntilRenewal,
                    'is_sent' => false,
                    'sent_at' => null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
        
        $this->command->info('Renewal reminders seeded successfully!');
    }
}