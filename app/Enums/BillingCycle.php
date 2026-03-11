<?php

namespace App\Enums;

enum BillingCycle: string
{
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';
    case YEARLY = 'yearly';
    case CUSTOM = 'custom';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::MONTHLY => 'Monthly',
            self::QUARTERLY => 'Quarterly',
            self::YEARLY => 'Yearly',
            self::CUSTOM => 'Custom',
        };
    }

    /**
     * Get default days for billing cycle
     */
    public function days(): int
    {
        return match($this) {
            self::MONTHLY => 30,
            self::QUARTERLY => 90,
            self::YEARLY => 365,
            self::CUSTOM => 0, // Custom days will be handled separately
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