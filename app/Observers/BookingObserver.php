<?php

namespace App\Observers;

use App\Models\Booking;
use App\Services\BookingNotificationService;

class BookingObserver
{
    public function updated(Booking $booking): void
    {
        $watched = ['starts_at', 'ends_at', 'classroom_id'];

        if (! $booking->wasChanged($watched)) {
            return;
        }

        app(BookingNotificationService::class)->bookingUpdated($booking, $this->changes($booking));
    }

    /**
     * @return array<string, string>
     */
    private function changes(Booking $booking): array
    {
        $changes = [];

        if ($booking->wasChanged('classroom_id')) {
            $changes['Room'] = $booking->classroom?->name ?? 'To be confirmed';
        }

        if ($booking->wasChanged(['starts_at', 'ends_at'])) {
            $changes['New schedule'] = optional($booking->starts_at)->format('M d, Y H:i')
                .' – '.optional($booking->ends_at)->format('H:i');
        }

        return $changes;
    }
}
