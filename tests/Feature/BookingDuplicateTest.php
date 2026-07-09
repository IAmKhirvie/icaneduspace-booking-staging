<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Classroom;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingDuplicateTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_customer_cannot_send_exact_duplicate_booking(): void
    {
        $room = Classroom::create([
            'name' => '11F AI Hub',
            'slug' => '11f-ai-hub',
            'location' => '11F Eduspace',
            'address' => 'Strata 100, Emerald Ave., Ortigas Center, Pasig City, Philippines',
            'capacity' => 24,
            'hourly_rate' => 800,
            'description' => 'Main room.',
            'is_active' => true,
        ]);

        $service = app(BookingService::class);

        [$morningStart, $morningEnd] = $service->resolveTimeBlock('2026-05-21', 'morning');

        $base = [
            'classroom_id' => $room->id,
            'service_package_id' => null,
            'booking_date' => '2026-05-21',
            'participant_count' => 2,
            'purpose' => 'Lecture',
            'format' => 'Offline',
            'customer_name' => 'Khirvie',
            'contact' => 'khirviecliffordbautista@gmail.com',
            'organization' => null,
            'customer_notes' => null,
        ];

        $service->createBooking([
            ...$base,
            'starts_at' => $morningStart,
            'ends_at' => $morningEnd,
        ]);

        $this->expectException(ValidationException::class);

        $service->createBooking([
            ...$base,
            'starts_at' => $morningStart,
            'ends_at' => $morningEnd,
        ]);
    }

    public function test_same_customer_must_confirm_additional_time_slot_on_same_room_and_date(): void
    {
        $room = Classroom::create([
            'name' => '11F AI Hub',
            'slug' => '11f-ai-hub',
            'location' => '11F Eduspace',
            'address' => 'Strata 100, Emerald Ave., Ortigas Center, Pasig City, Philippines',
            'capacity' => 24,
            'hourly_rate' => 800,
            'description' => 'Main room.',
            'is_active' => true,
        ]);

        $service = app(BookingService::class);

        [$morningStart, $morningEnd] = $service->resolveTimeBlock('2026-05-21', 'morning');
        [$eveningStart, $eveningEnd] = $service->resolveTimeBlock('2026-05-21', 'evening');

        $base = [
            'classroom_id' => $room->id,
            'service_package_id' => null,
            'booking_date' => '2026-05-21',
            'participant_count' => 2,
            'purpose' => 'Lecture',
            'format' => 'Offline',
            'customer_name' => 'Khirvie',
            'contact' => 'khirviecliffordbautista@gmail.com',
            'organization' => null,
            'customer_notes' => null,
        ];

        $service->createBooking([
            ...$base,
            'starts_at' => $morningStart,
            'ends_at' => $morningEnd,
        ]);

        try {
            $service->createBooking([
                ...$base,
                'starts_at' => $eveningStart,
                'ends_at' => $eveningEnd,
            ]);

            $this->fail('Expected confirmation validation error.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('confirm_additional_booking', $exception->errors());
        }

        $booking = $service->createBooking([
            ...$base,
            'starts_at' => $eveningStart,
            'ends_at' => $eveningEnd,
            'confirm_additional_booking' => true,
        ]);

        $this->assertNotNull($booking->id);
    }

    public function test_daily_range_creates_one_booking_per_date_with_same_time_block(): void
    {
        $room = Classroom::create([
            'name' => '11F AI Hub',
            'slug' => '11f-ai-hub',
            'location' => '11F Eduspace',
            'address' => 'Strata 100, Emerald Ave., Ortigas Center, Pasig City, Philippines',
            'capacity' => 24,
            'hourly_rate' => 800,
            'description' => 'Main room.',
            'is_active' => true,
        ]);

        $service = app(BookingService::class);

        $bookings = $service->createDailyBookings([
            'classroom_id' => $room->id,
            'service_package_id' => null,
            'booking_date' => '2026-05-21',
            'booking_end_date' => '2026-05-23',
            'time_block' => 'afternoon',
            'participant_count' => 2,
            'purpose' => 'Daily lecture',
            'format' => 'Offline',
            'customer_name' => 'Khirvie',
            'contact' => 'khirviecliffordbautista@gmail.com',
            'organization' => null,
            'customer_notes' => null,
        ]);

        $this->assertCount(3, $bookings);
        $this->assertSame(3, Booking::where('purpose', 'Daily lecture')->count());

        $this->assertSame(
            ['2026-05-21', '2026-05-22', '2026-05-23'],
            Booking::where('purpose', 'Daily lecture')
                ->orderBy('booking_date')
                ->get()
                ->map(fn (Booking $booking) => $booking->booking_date->toDateString())
                ->all()
        );

        $this->assertTrue($bookings[0]->starts_at->isSameDay('2026-05-21'));
        $this->assertSame('13:00', $bookings[0]->starts_at->format('H:i'));
        $this->assertSame('17:00', $bookings[0]->ends_at->format('H:i'));
    }
}
