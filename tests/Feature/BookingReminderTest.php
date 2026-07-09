<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Classroom;
use App\Notifications\BookingReminder;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_sends_reminders_for_approved_bookings_starting_soon(): void
    {
        Notification::fake();

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

        $booking = Booking::create([
            'classroom_id' => $room->id,
            'status' => BookingService::STATUS_APPROVED,
            'booking_date' => now()->toDateString(),
            'starts_at' => now()->addMinutes(8),
            'ends_at' => now()->addHours(3),
            'participant_count' => 2,
            'purpose' => 'Lecture',
            'format' => 'Offline',
            'customer_name' => 'Khirvie',
            'contact' => 'khirviecliffordbautista@gmail.com',
        ]);

        $this->artisan('bookings:send-reminders')
            ->expectsOutput('Sent 1 booking reminder(s).')
            ->assertSuccessful();

        Notification::assertSentOnDemand(BookingReminder::class);
        $this->assertNotNull($booking->fresh()->reminder_sent_at);
    }
}
