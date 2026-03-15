<?php

namespace App\Enums;

enum SupportLevel: string
{
    case STANDARD = 'standard';
    case PREMIUM = 'premium';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::STANDARD => 'Standard Support',
            self::PREMIUM => 'Premium Support',
        };
    }

    /**
     * Get SLA response time in hours
     */
    public function responseTime(): int
    {
        return match($this) {
            self::STANDARD => 24, // 24 hours
            self::PREMIUM => 4,   // 4 hours
        };
    }

    /**
     * Get SLA resolution time in hours
     */
    public function resolutionTime(): int
    {
        return match($this) {
            self::STANDARD => 72, // 3 days
            self::PREMIUM => 24,  // 1 day
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