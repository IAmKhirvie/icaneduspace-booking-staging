<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\BookingNotificationService;
use App\Services\BookingService;
use Illuminate\Console\Command;

class SendBookingReminders extends Command
{
    protected $signature = 'bookings:send-reminders';

    protected $description = 'Send booking reminders shortly before approved bookings start.';

    public function handle(BookingNotificationService $notifications): int
    {
        $bookings = Booking::query()
            ->with('classroom')
            ->where('status', BookingService::STATUS_APPROVED)
            ->whereNull('reminder_sent_at')
            ->whereBetween('starts_at', [now(), now()->addMinutes(10)])
            ->get();

        foreach ($bookings as $booking) {
            $notifications->bookingReminder($booking);

            $booking->forceFill([
                'reminder_sent_at' => now(),
            ])->saveQuietly();
        }

        $this->info("Sent {$bookings->count()} booking reminder(s).");

        return self::SUCCESS;
    }
}
