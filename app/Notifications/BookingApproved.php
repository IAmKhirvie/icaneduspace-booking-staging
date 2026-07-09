<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\BookingSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

class BookingApproved extends Notification
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
        $where = trim(implode(' · ', array_filter([
            $b->classroom?->address,
            $b->classroom?->floor ? 'Floor '.$b->classroom->floor : null,
            $b->classroom?->room_number ? 'Room '.$b->classroom->room_number : null,
        ])));

        $mail = (new MailMessage)
            ->subject("Your ICAN Eduspace booking is confirmed · #{$b->id}")
            ->greeting("Hi {$b->customer_name},")
            ->line('Your booking has been **confirmed**.')
            ->line('**Room:** '.($b->classroom?->name ?? '—'))
            ->line('**Where:** '.($where !== '' ? $where : 'To be confirmed'))
            ->line('**When:** '.optional($b->starts_at)->format('M d, Y H:i').' – '.optional($b->ends_at)->format('H:i'));

        if ($b->reservation_fee_amount) {
            $mail->line('**Reservation fee:** '.$b->reservationFeeStatusLabel().' · '.\App\Support\Money::format($b->reservation_fee_amount));
        }

        if ($b->equipmentRequestLabels()) {
            $mail->line('**Equipment:** '.implode(', ', $b->equipmentRequestLabels()));
        }

        if ($b->snackBeverageRequestLabels()) {
            $mail->line('**Coffee and snacks:** '.implode(', ', $b->snackBeverageRequestLabels()));
        }

        $arrivalInstructions = $b->classroom?->arrival_instructions
            ?: BookingSetting::getValue(BookingSetting::ARRIVAL_INSTRUCTIONS, '');

        if ($arrivalInstructions) {
            $mail->line('**Arrival instructions:** '.$arrivalInstructions);
        }

        $mail
            ->line('We will see you then. Reply to this email if anything changes.')
            ->salutation('— ICAN Eduspace');

        if ($b->starts_at && $b->ends_at) {
            $ics = Calendar::create('ICAN Eduspace')
                ->event(
                    Event::create("ICAN Eduspace · {$b->purpose}")
                        ->startsAt($b->starts_at)
                        ->endsAt($b->ends_at)
                        ->address(trim(($b->classroom?->name ?? '').' '.($b->classroom?->address ?? $b->classroom?->location ?? '')))
                        ->description($b->customer_notes ?? '')
                )
                ->get();

            $mail->attachData($ics, "ican-booking-{$b->id}.ics", [
                'mime' => 'text/calendar; charset=UTF-8',
            ]);
        }

        return $mail;
    }
}
