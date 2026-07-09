<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingNotification;
use App\Models\User;
use App\Notifications\BookingApproved;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingReceived;
use App\Notifications\BookingRejected;
use App\Notifications\BookingReminder;
use App\Notifications\BookingRescheduledToStaff;
use App\Notifications\BookingSubmittedToStaff;
use App\Notifications\BookingUpdated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

class BookingNotificationService
{
    public function bookingReceived(Booking $booking): bool
    {
        $customerSent = $this->notifyCustomer($booking, new BookingReceived($booking));
        $staffSent = $this->notifyStaff($booking, new BookingSubmittedToStaff($booking));

        return $customerSent || $staffSent;
    }

    public function bookingApproved(Booking $booking): bool
    {
        return $this->notifyCustomer($booking, new BookingApproved($booking));
    }

    public function bookingRejected(Booking $booking, ?string $reason = null): bool
    {
        return $this->notifyCustomer($booking, new BookingRejected($booking, $reason));
    }

    public function bookingCancelled(Booking $booking): bool
    {
        return $this->notifyCustomer($booking, new BookingCancelled($booking));
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    public function bookingUpdated(Booking $booking, array $changes = []): bool
    {
        return $this->notifyCustomer($booking, new BookingUpdated($booking, $changes));
    }

    public function bookingReminder(Booking $booking): bool
    {
        return $this->notifyCustomer($booking, new BookingReminder($booking));
    }

    public function bookingRescheduledForStaff(Booking $booking): bool
    {
        return $this->notifyStaff($booking, new BookingRescheduledToStaff($booking));
    }

    private function notifyCustomer(Booking $booking, object $notification): bool
    {
        $email = $booking->contact_email_or_fallback();
        $record = $this->recordNotification(
            booking: $booking,
            audience: BookingNotification::AUDIENCE_CUSTOMER,
            notification: $notification,
            recipient: $email,
            userId: $booking->user_id,
        );

        if (! $email) {
            $this->markRecords([$record], BookingNotification::STATUS_SKIPPED, 'No customer email available.');

            Log::warning('Booking notification skipped: no customer email.', [
                'booking_id' => $booking->id,
            ]);

            return false;
        }

        return $this->sendSafely(fn () => Notification::route('mail', $email)->notify($notification), [
            'booking_id' => $booking->id,
            'recipient' => $email,
            'notification' => $notification::class,
        ], [$record]);
    }

    private function notifyStaff(Booking $booking, object $notification): bool
    {
        $staff = User::whereHas('roles', fn ($query) => $query->whereIn('name', ['admin', 'super_admin', 'staff']))->get();

        if ($staff->isEmpty()) {
            $record = $this->recordNotification(
                booking: $booking,
                audience: BookingNotification::AUDIENCE_STAFF,
                notification: $notification,
                recipient: 'staff',
            );

            $this->markRecords([$record], BookingNotification::STATUS_SKIPPED, 'No staff users available.');

            return false;
        }

        $records = $staff
            ->map(fn (User $user) => $this->recordNotification(
                booking: $booking,
                audience: BookingNotification::AUDIENCE_STAFF,
                notification: $notification,
                recipient: $user->email,
                userId: $user->id,
            ))
            ->all();

        return $this->sendSafely(fn () => Notification::send($staff, $notification), [
            'booking_id' => $booking->id,
            'recipient' => 'staff',
            'notification' => $notification::class,
        ], $records);
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<int, BookingNotification>  $records
     */
    private function sendSafely(callable $callback, array $context, array $records = []): bool
    {
        if (! app()->environment('testing') && in_array((string) config('mail.default'), ['log', 'array'], true)) {
            $this->markRecords($records, BookingNotification::STATUS_SKIPPED, 'Outbound mailer is not configured.');

            Log::warning('Booking email notification skipped: outbound mailer is not configured.', [
                ...$context,
                'mailer' => config('mail.default'),
            ]);

            return false;
        }

        try {
            $callback();
            $this->markRecords($records, BookingNotification::STATUS_SENT);

            return true;
        } catch (Throwable $exception) {
            $this->markRecords($records, BookingNotification::STATUS_FAILED, $exception->getMessage());

            Log::error('Booking email notification failed.', [
                ...$context,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function recordNotification(
        Booking $booking,
        string $audience,
        object $notification,
        ?string $recipient = null,
        ?int $userId = null,
    ): BookingNotification {
        return BookingNotification::create([
            'booking_id' => $booking->id,
            'user_id' => $userId,
            'audience' => $audience,
            'channel' => 'mail',
            'notification_type' => $notification::class,
            'recipient' => $recipient,
            'subject' => Str::headline(class_basename($notification)),
            'message' => $booking->workflowStageLabel(),
            'status' => BookingNotification::STATUS_PENDING,
            'data' => [
                'booking_status' => $booking->status,
                'workflow_stage' => $booking->workflowStage(),
                'payment_scope' => $booking->paymentScopeFormValue(),
            ],
        ]);
    }

    /**
     * @param  array<int, BookingNotification>  $records
     */
    private function markRecords(array $records, string $status, ?string $error = null): void
    {
        foreach ($records as $record) {
            $record->update([
                'status' => $status,
                'error' => $error,
                'sent_at' => $status === BookingNotification::STATUS_SENT ? now() : $record->sent_at,
            ]);
        }
    }
}
