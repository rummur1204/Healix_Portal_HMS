<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case BANK = 'bank';
    case ONLINE = 'online';
    case OTHER = 'other';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::CASH => 'Cash',
            self::BANK => 'Bank Transfer',
            self::ONLINE => 'Online Payment',
            self::OTHER => 'Other',
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