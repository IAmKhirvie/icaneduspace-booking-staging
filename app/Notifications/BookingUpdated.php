<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingUpdated extends Notification
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $changes
     */
    public function __construct(
        public Booking $booking,
        public array $changes = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $b = $this->booking->loadMissing(['classroom', 'servicePackage']);

        $mail = (new MailMessage)
            ->subject("Your ICAN Eduspace booking was updated · #{$b->id}")
            ->greeting("Hi {$b->customer_name},")
            ->line('There was an update to your booking details.')
            ->line('**Reference:** #'.$b->id)
            ->line('**Room:** '.($b->classroom?->name ?? '—'))
            ->line('**Date:** '.optional($b->starts_at)->format('M d, Y'))
            ->line('**Time:** '.optional($b->starts_at)->format('H:i').' – '.optional($b->ends_at)->format('H:i'));

        if (! empty($this->changes)) {
            foreach ($this->changes as $label => $value) {
                $mail->line("**{$label}:** {$value}");
            }
        }

        return $mail
            ->line('If this is not what you expected, please reply to this email.')
            ->salutation('— ICAN Eduspace');
    }
}
