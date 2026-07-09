<?php

namespace Tests\Unit;

use App\Models\Classroom;
use App\Models\ServicePackage;
use App\Services\BookingService;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    public function test_it_estimates_price_from_positive_booking_duration(): void
    {
        $service = app(BookingService::class);
        [$startsAt, $endsAt] = $service->resolveTimeBlock('2026-05-21', 'morning');

        $price = $service->estimatePrice(
            new Classroom(['hourly_rate' => 800]),
            new ServicePackage(['base_price' => 1200]),
            $startsAt,
            $endsAt,
        );

        $this->assertSame(3600, $price);
    }
}
