<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\BookingSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingReceived extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $b = $this->booking->loadMissing(['classroom', 'servicePackage']);

        $mail = (new MailMessage)
            ->subject('We received your ICAN Eduspace booking request')
            ->greeting("Hi {$b->customer_name},")
            ->line('Thanks for your request. We will confirm availability shortly.')
            ->line("**Reference:** #{$b->id}")
            ->line('**Room:** '.($b->classroom?->name ?? '—'))
            ->line('**Date:** '.optional($b->starts_at)->format('M d, Y'))
            ->line('**Time:** '.optional($b->starts_at)->format('H:i').' – '.optional($b->ends_at)->format('H:i'))
            ->line('**Format:** '.$b->format);

	        if ($b->estimated_price !== null) {
	            $mail->line('**Estimated total:** '.\App\Support\Money::format($b->estimated_price));
	        }

	        if ($b->special_discount_amount) {
	            $mail->line('**Special discount:** -'.\App\Support\Money::format($b->special_discount_amount).' ('.$b->special_discount_percent.'%)');
	        }

        if ($b->reservation_fee_amount) {
            $mail->line('**Reservation fee:** '.\App\Support\Money::format($b->reservation_fee_amount).' ('.$b->reservation_fee_percent.'%)');
        }

        $mail->line('**Preferred payment method:** '.$b->paymentMethodLabel());

        if ($b->equipmentRequestLabels()) {
            $mail->line('**Equipment:** '.implode(', ', $b->equipmentRequestLabels()));
        }

        if ($b->snackBeverageRequestLabels()) {
            $mail->line('**Coffee and snacks:** '.implode(', ', $b->snackBeverageRequestLabels()));
        }

        if ($instructions = BookingSetting::getValue(BookingSetting::PAYMENT_INSTRUCTIONS, '')) {
            $mail->line('**Payment instructions:** '.$instructions);
        }

        return $mail->salutation('— ICAN Eduspace');
    }
}
