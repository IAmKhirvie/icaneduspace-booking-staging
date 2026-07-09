<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingReminder extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $b = $this->booking->loadMissing(['classroom']);

        return (new MailMessage)
            ->subject("Your ICAN Eduspace booking starts soon · #{$b->id}")
            ->greeting("Hi {$b->customer_name},")
            ->line('Your booking starts in about 10 minutes.')
            ->line('**Room:** '.($b->classroom?->name ?? '—'))
            ->line('**Location:** '.($b->classroom?->address ?? 'Strata 100, Emerald Ave., Ortigas Center, Pasig City, Philippines'))
            ->line('**Time:** '.optional($b->starts_at)->format('M d, Y H:i').' – '.optional($b->ends_at)->format('H:i'))
            ->line('Please proceed to the room so staff can assist you on time.')
            ->salutation('— ICAN Eduspace');
    }
}
