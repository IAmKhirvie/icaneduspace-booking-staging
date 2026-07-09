<?php

namespace App\Support;

class Money
{
    /**
     * Base currency for stored prices.
     */
    public const BASE = 'PHP';

    /**
     * Format a base-currency amount for display.
     *
     * Pass $target = 'KRW' (or set ?currency=krw / locale ko) to convert via PHP_TO_KRW_RATE.
     */
    public static function format(int|float|null $amount, ?string $target = null): string
    {
        if ($amount === null) {
            return '—';
        }

        $target = strtoupper($target ?? self::detectTarget());

        return match ($target) {
            'KRW' => '₩'.number_format((int) round($amount * (float) env('PHP_TO_KRW_RATE', 23.5))),
            default => '₱'.number_format((float) $amount, 0),
        };
    }

    /**
     * Detect the user's preferred display currency from session / query / locale.
     */
    public static function detectTarget(): string
    {
        $q = request()?->query('currency');
        if (is_string($q)) {
            return strtoupper($q);
        }

        if (session()?->has('display_currency')) {
            return strtoupper(session('display_currency'));
        }

        if (app()->getLocale() === 'ko') {
            return 'KRW';
        }

        return self::BASE;
    }
}
