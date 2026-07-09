<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingSubmittedToStaff extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $b   = $this->booking->loadMissing(['classroom', 'servicePackage']);
        $url = route('manage.bookings.index', ['q' => '#'.$b->id]);

        $mail = (new MailMessage)
            ->subject("New booking request · #{$b->id} · {$b->customer_name}")
            ->line("**{$b->customer_name}** requested **".($b->classroom?->name ?? 'a room').'**.')
            ->line('**When:** '.optional($b->starts_at)->format('M d, Y H:i').' – '.optional($b->ends_at)->format('H:i'))
            ->line('**Purpose:** '.$b->purpose)
            ->line('**Contact:** '.$b->contact);

	        if ($b->estimated_price !== null) {
	            $mail->line('**Estimated total:** '.\App\Support\Money::format($b->estimated_price));
	        }

	        if ($b->special_discount_amount) {
	            $mail->line('**Special discount:** -'.\App\Support\Money::format($b->special_discount_amount).' ('.$b->special_discount_percent.'%)');
	        }

        if ($b->reservation_fee_amount) {
            $mail->line('**Reservation fee:** '.\App\Support\Money::format($b->reservation_fee_amount).' · '.$b->reservationFeeStatusLabel());
        }

        $mail->line('**Preferred payment method:** '.$b->paymentMethodLabel());

        if ($b->equipmentRequestLabels()) {
            $mail->line('**Equipment:** '.implode(', ', $b->equipmentRequestLabels()));
        }

        if ($b->snackBeverageRequestLabels()) {
            $mail->line('**Coffee and snacks:** '.implode(', ', $b->snackBeverageRequestLabels()));
        }

        return $mail
            ->action('Review in admin', $url)
            ->line('Confirm or reject so the customer hears back today.');
    }
}
