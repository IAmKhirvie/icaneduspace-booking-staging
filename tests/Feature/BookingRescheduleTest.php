<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Classroom;
use App\Models\User;
use App\Notifications\BookingUpdated;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BookingRescheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_reschedule_own_active_booking_and_it_returns_to_pending(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'customer@example.com']);
        $room = $this->room('11F AI Hub', '11f-ai-hub');
        $service = app(BookingService::class);
        [$start, $end] = $service->resolveTimeBlock(now()->addDay()->toDateString(), 'morning');

        $booking = Booking::create([
            'user_id' => $user->id,
            'classroom_id' => $room->id,
            'status' => BookingService::STATUS_APPROVED,
            'booking_date' => $start->toDateString(),
            'starts_at' => $start,
            'ends_at' => $end,
            'reminder_sent_at' => now(),
            'participant_count' => 2,
            'purpose' => 'Lecture',
            'format' => 'Offline',
            'customer_name' => 'Customer',
            'contact' => 'customer@example.com',
        ]);

        $this->actingAs($user)
            ->post(route('bookings.reschedule', $booking), [
                'classroom_id' => $room->id,
                'booking_date' => now()->addDays(2)->toDateString(),
                'time_block' => 'evening',
                'reschedule_note' => 'Need a later slot.',
            ])
            ->assertRedirect(route('bookings.show', $booking));

        $booking->refresh();

        $this->assertSame(BookingService::STATUS_PENDING, $booking->status);
        $this->assertSame('18:00', $booking->starts_at->format('H:i'));
        $this->assertNull($booking->approved_at);
        $this->assertNull($booking->reminder_sent_at);
        $this->assertStringContainsString('Need a later slot.', $booking->customer_notes);
        Notification::assertSentOnDemand(BookingUpdated::class);
    }

    public function test_staff_can_reschedule_booking_without_returning_it_to_pending(): void
    {
        Notification::fake();

        Role::create(['name' => 'staff']);

        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $customer = User::factory()->create(['email' => 'customer@example.com']);
        $room = $this->room('11F AI Hub', '11f-ai-hub');
        $service = app(BookingService::class);
        [$start, $end] = $service->resolveTimeBlock(now()->addDay()->toDateString(), 'morning');

        $booking = Booking::create([
            'user_id' => $customer->id,
            'classroom_id' => $room->id,
            'status' => BookingService::STATUS_APPROVED,
            'booking_date' => $start->toDateString(),
            'starts_at' => $start,
            'ends_at' => $end,
            'participant_count' => 2,
            'purpose' => 'Lecture',
            'format' => 'Offline',
            'customer_name' => 'Customer',
            'contact' => 'customer@example.com',
            'approved_at' => now(),
        ]);

        $this->actingAs($staff)
            ->post(route('bookings.reschedule', $booking), [
                'classroom_id' => $room->id,
                'booking_date' => now()->addDays(3)->toDateString(),
                'time_block' => 'afternoon',
            ])
            ->assertRedirect(route('bookings.show', $booking));

        $booking->refresh();

        $this->assertSame(BookingService::STATUS_APPROVED, $booking->status);
        $this->assertSame('13:00', $booking->starts_at->format('H:i'));
        $this->assertNotNull($booking->approved_at);
        Notification::assertSentOnDemand(BookingUpdated::class);
    }

    public function test_reschedule_rejects_conflicting_room_time(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'customer@example.com']);
        $room = $this->room('11F AI Hub', '11f-ai-hub');
        $service = app(BookingService::class);
        $date = now()->addDay()->toDateString();
        [$morningStart, $morningEnd] = $service->resolveTimeBlock($date, 'morning');
        [$eveningStart, $eveningEnd] = $service->resolveTimeBlock($date, 'evening');

        Booking::create([
            'classroom_id' => $room->id,
            'status' => BookingService::STATUS_PENDING,
            'booking_date' => $date,
            'starts_at' => $morningStart,
            'ends_at' => $morningEnd,
            'purpose' => 'Existing booking',
            'format' => 'Offline',
            'customer_name' => 'Other',
            'contact' => 'other@example.com',
        ]);

        $booking = Booking::create([
            'user_id' => $user->id,
            'classroom_id' => $room->id,
            'status' => BookingService::STATUS_PENDING,
            'booking_date' => $date,
            'starts_at' => $eveningStart,
            'ends_at' => $eveningEnd,
            'purpose' => 'Lecture',
            'format' => 'Offline',
            'customer_name' => 'Customer',
            'contact' => 'customer@example.com',
        ]);

        $this->actingAs($user)
            ->from(route('bookings.reschedule.edit', $booking))
            ->post(route('bookings.reschedule', $booking), [
                'classroom_id' => $room->id,
                'booking_date' => $date,
                'time_block' => 'morning',
            ])
            ->assertRedirect(route('bookings.reschedule.edit', $booking))
            ->assertSessionHasErrors('classroom_id');
    }

    private function room(string $name, string $slug): Classroom
    {
        return Classroom::create([
            'name' => $name,
            'slug' => $slug,
            'location' => '11F Eduspace',
            'address' => 'Strata 100, Emerald Ave., Ortigas Center, Pasig City, Philippines',
            'capacity' => 24,
            'hourly_rate' => 800,
            'description' => 'Main room.',
            'is_active' => true,
        ]);
    }
}
