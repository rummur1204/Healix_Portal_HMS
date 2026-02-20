<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PAID = 'paid';
    case UNPAID = 'unpaid';
    case PARTIAL = 'partial';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::PAID => 'Paid',
            self::UNPAID => 'Unpaid',
            self::PARTIAL => 'Partial Payment',
        };
    }

    /**
     * Get status color for UI
     */
    public function color(): string
    {
        return match($this) {
            self::PAID => 'green',
            self::UNPAID => 'red',
            self::PARTIAL => 'yellow',
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