<?php

namespace App\Enums;

enum SubscriptionAction: string
{
    case CREATED = 'created';
    case RENEWED = 'renewed';
    case UPGRADED = 'upgraded';
    case DOWNGRADED = 'downgraded';
    case CANCELLED = 'cancelled';
    case SUSPENDED = 'suspended';
    case REACTIVATED = 'reactivated';
    case STATUS_CHANGED = 'status_changed';
    case PAYMENT_RECEIVED = 'payment_received';
    case PLAN_CHANGED = 'plan_changed';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::CREATED => 'Subscription Created',
            self::RENEWED => 'Subscription Renewed',
            self::UPGRADED => 'Plan Upgraded',
            self::DOWNGRADED => 'Plan Downgraded',
            self::CANCELLED => 'Subscription Cancelled',
            self::SUSPENDED => 'Subscription Suspended',
            self::REACTIVATED => 'Subscription Reactivated',
            self::STATUS_CHANGED => 'Status Changed',
            self::PAYMENT_RECEIVED => 'Payment Received',
            self::PLAN_CHANGED => 'Plan Changed',
        };
    }

    /**
     * Get all values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get select options for forms
     */
    public static function options(): array
    {
        return array_reduce(self::cases(), function ($options, $case) {
            $options[$case->value] = $case->label();
            return $options;
        }, []);
    }
}