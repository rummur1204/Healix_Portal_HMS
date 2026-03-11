<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case TRIAL = 'trial';
    case ACTIVE = 'active';
    case PAST_DUE = 'past_due';
    case CANCELLED = 'cancelled';
    case SUSPENDED = 'suspended';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::TRIAL => 'Trial',
            self::ACTIVE => 'Active',
            self::PAST_DUE => 'Past Due',
            self::CANCELLED => 'Cancelled',
            self::SUSPENDED => 'Suspended',
        };
    }

    /**
     * Get status color for UI badges
     */
    public function color(): string
    {
        return match($this) {
            self::TRIAL => 'blue',
            self::ACTIVE => 'green',
            self::PAST_DUE => 'orange',
            self::CANCELLED => 'red',
            self::SUSPENDED => 'gray',
        };
    }

    /**
     * Get hex color code
     */
    public function hexColor(): string
    {
        return match($this) {
            self::TRIAL => '#3b82f6', // blue
            self::ACTIVE => '#22c55e', // green
            self::PAST_DUE => '#f97316', // orange
            self::CANCELLED => '#ef4444', // red
            self::SUSPENDED => '#6b7280', // gray
        };
    }

    /**
     * Check if status allows client access
     */
    public function allowsAccess(): bool
    {
        return in_array($this, [self::TRIAL, self::ACTIVE]);
    }

    /**
     * Check if status is renewable
     */
    public function isRenewable(): bool
    {
        return in_array($this, [self::ACTIVE, self::PAST_DUE]);
    }

    /**
     * Get all values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all labels as array
     */
    public static function labels(): array
    {
        return array_map(fn($case) => $case->label(), self::cases());
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