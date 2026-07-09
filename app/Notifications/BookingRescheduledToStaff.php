<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingRescheduledToStaff extends Notification
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
            ->subject("Reschedule request · #{$b->id} · {$b->customer_name}")
            ->line("**{$b->customer_name}** changed their booking schedule and it is waiting for review.")
            ->line('**Room:** '.($b->classroom?->name ?? '—'))
            ->line('**New time:** '.optional($b->starts_at)->format('M d, Y H:i').' – '.optional($b->ends_at)->format('H:i'))
            ->line('**Contact:** '.$b->contact)
            ->action('Review booking', route('manage.bookings.index', ['q' => '#'.$b->id]))
            ->line('Approve, reject, or contact the customer if this new schedule needs changes.');
    }
}
