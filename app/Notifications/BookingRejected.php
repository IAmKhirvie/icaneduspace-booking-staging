<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingRejected extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking, public ?string $reason = null) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $b = $this->booking;

        $mail = (new MailMessage)
            ->subject("Update on your ICAN Eduspace booking · #{$b->id}")
            ->greeting("Hi {$b->customer_name},")
            ->line('Unfortunately we are unable to confirm your booking at the requested time.');

        if ($this->reason) {
            $mail->line('**Note from staff:** '.$this->reason);
        }

        return $mail
            ->line('You are welcome to submit a new request with a different time block.')
            ->action('Send a new request', url('/#contact'))
            ->salutation('— ICAN Eduspace');
    }
}
