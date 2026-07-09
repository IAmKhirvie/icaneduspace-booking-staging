<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCancelled extends Notification
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

        $mail = (new MailMessage)
            ->subject("Booking cancelled · #{$b->id}")
            ->greeting("Hi {$b->customer_name},")
            ->line('Your booking has been cancelled.')
            ->line('**Room:** '.($b->classroom?->name ?? '—'))
            ->line('**When:** '.optional($b->starts_at)->format('M d, Y H:i'));

        if ($b->cancellation_reason) {
            $mail->line('**Reason:** '.$b->cancellation_reason);
        }

        if ($b->wasCancelledOnScheduledDay()) {
            $mail->line('This was cancelled on the scheduled date, so staff may need to review any preparation already made.');
        }

        return $mail
            ->line('Need a new slot? Send a fresh request any time.')
            ->action('Book again', url('/#contact'))
            ->salutation('— ICAN Eduspace');
    }
}
