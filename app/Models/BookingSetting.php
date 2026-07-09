<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingSetting extends Model
{
    public const RESERVATION_FEE_PERCENT = 'reservation_fee_percent';
    public const RESERVATION_FEE_MAX_PERCENT = 50.0;
    public const SPECIAL_DISCOUNT_PERCENT = 'special_discount_percent';
    public const SPECIAL_DISCOUNT_MAX_PERCENT = 100.0;
    public const PAYMENT_INSTRUCTIONS = 'payment_instructions';
    public const ARRIVAL_INSTRUCTIONS = 'arrival_instructions';

    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function setValue(string $key, string|int|float|null $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value === null ? null : (string) $value],
        );
    }

    public static function reservationFeePercent(): float
    {
        $percent = (float) static::getValue(self::RESERVATION_FEE_PERCENT, '30');

        return max(0, min(self::RESERVATION_FEE_MAX_PERCENT, $percent));
    }

    public static function specialDiscountPercent(): float
    {
        $percent = (float) static::getValue(self::SPECIAL_DISCOUNT_PERCENT, '0');

        return max(0, min(self::SPECIAL_DISCOUNT_MAX_PERCENT, $percent));
    }
}
