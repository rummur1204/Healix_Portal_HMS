<?php

namespace App\Exports;

use App\Models\ClientSubscription;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RenewalsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $renewals;

    public function __construct($renewals)
    {
        $this->renewals = $renewals;
    }

    public function collection()
    {
        return $this->renewals;
    }

    public function headings(): array
    {
        return [
            'Subscription ID',
            'Client',
            'Plan',
            'Renewal Date',
            'Amount',
            'Status',
        ];
    }

    public function map($subscription): array
    {
        return [
            $subscription->subscription_id,
            $subscription->client->organization_name,
            $subscription->plan->plan_name,
            $subscription->renewal_date->format('Y-m-d'),
            $subscription->plan->price,
            $subscription->status,
        ];
    }
}