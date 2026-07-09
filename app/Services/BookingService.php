<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingSetting;
use App\Models\BookingSpecialCase;
use App\Models\Classroom;
use App\Models\ServicePackage;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class BookingService
{
    public const MAX_DAILY_RANGE_DAYS = 31;

    public const TIME_BLOCKS = [
        'morning'   => ['09:00', '12:00'],
        'afternoon' => ['13:00', '17:00'],
        'evening'   => ['18:00', '21:00'],
    ];

    public const STATUS_PENDING   = 'pending';
    public const STATUS_APPROVED  = 'approved';
    public const STATUS_REJECTED  = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    public const ACTIVE_STATUSES = [self::STATUS_PENDING, self::STATUS_APPROVED];

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_CANCELLED,
        self::STATUS_COMPLETED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_APPROVED => 'Booked',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_CANCELLED => 'Cancelled',
        self::STATUS_COMPLETED => 'Completed',
    ];

    /**
     * Resolve a time block code into a start/end window for the given date.
     *
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    public function resolveTimeBlock(string $date, string $block): array
    {
        if (! isset(self::TIME_BLOCKS[$block])) {
            throw new RuntimeException("Unknown time block: {$block}");
        }

        [$start, $end] = self::TIME_BLOCKS[$block];

        return [
            CarbonImmutable::parse("{$date} {$start}"),
            CarbonImmutable::parse("{$date} {$end}"),
        ];
    }

    /**
     * @return array<int, string>
     */
    public function dailyDatesForRange(string $startDate, ?string $endDate = null): array
    {
        $start = CarbonImmutable::parse($startDate)->startOfDay();
        $end = filled($endDate)
            ? CarbonImmutable::parse((string) $endDate)->startOfDay()
            : $start;

        if ($end->lt($start)) {
            throw ValidationException::withMessages([
                'booking_end_date' => 'The end date must be the same as or after the start date.',
            ]);
        }

        $days = (int) $start->diffInDays($end) + 1;

        if ($days > self::MAX_DAILY_RANGE_DAYS) {
            throw ValidationException::withMessages([
                'booking_end_date' => 'Daily booking ranges can be up to '.self::MAX_DAILY_RANGE_DAYS.' days.',
            ]);
        }

        $dates = [];

        for ($date = $start; $date->lte($end); $date = $date->addDay()) {
            $dates[] = $date->toDateString();
        }

        return $dates;
    }

    /**
     * Is the room free in the given window? Honors pending + approved bookings.
     */
    public function isClassroomAvailable(?int $classroomId, CarbonImmutable $startsAt, CarbonImmutable $endsAt, ?int $ignoreBookingId = null): bool
    {
        if (! $classroomId) {
            return true;
        }

        return ! Booking::query()
            ->where('classroom_id', $classroomId)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->when($ignoreBookingId, fn ($q) => $q->where('id', '!=', $ignoreBookingId))
            ->where(function ($q) use ($startsAt, $endsAt) {
                $q->where('starts_at', '<', $endsAt)
                  ->where('ends_at', '>', $startsAt);
            })
            ->exists();
    }

    public function hasExactDuplicateBooking(array $attributes, ?int $userId = null, ?int $ignoreBookingId = null): bool
    {
        if (empty($attributes['classroom_id']) || empty($attributes['booking_date']) || empty($attributes['starts_at']) || empty($attributes['ends_at'])) {
            return false;
        }

        return $this->matchingCustomerBookings($attributes, $userId, $ignoreBookingId)
            ->where('starts_at', $attributes['starts_at'])
            ->where('ends_at', $attributes['ends_at'])
            ->exists();
    }

    public function hasAdditionalDailyBooking(array $attributes, ?int $userId = null, ?int $ignoreBookingId = null): bool
    {
        return $this->matchingCustomerBookings($attributes, $userId, $ignoreBookingId)->exists();
    }

    private function matchingCustomerBookings(array $attributes, ?int $userId = null, ?int $ignoreBookingId = null): \Illuminate\Database\Eloquent\Builder
    {
        if (empty($attributes['classroom_id']) || empty($attributes['booking_date'])) {
            return Booking::query()->whereRaw('1 = 0');
        }

        $contact = trim((string) ($attributes['contact'] ?? ''));

        if ($contact === '' && ! $userId) {
            return Booking::query()->whereRaw('1 = 0');
        }

        return Booking::query()
            ->where('classroom_id', $attributes['classroom_id'])
            ->whereDate('booking_date', $attributes['booking_date'])
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->when($ignoreBookingId, fn ($q) => $q->where('id', '!=', $ignoreBookingId))
            ->where(function ($q) use ($contact, $userId) {
                if ($contact !== '') {
                    $q->whereRaw('lower(contact) = ?', [mb_strtolower($contact)]);
                }

                if ($userId) {
                    $method = $contact !== '' ? 'orWhere' : 'where';
                    $q->{$method}('user_id', $userId);
                }
            });
    }

    /**
     * Estimate the price in the same minor-currency unit used by classroom.hourly_rate / package.base_price.
     */
    public function estimatePrice(?Classroom $classroom, ?ServicePackage $package, CarbonImmutable $startsAt, CarbonImmutable $endsAt): int
    {
        $minutes = max(0, $startsAt->diffInMinutes($endsAt));
        $hours   = $minutes / 60;

        $room    = $classroom ? (int) round($classroom->hourly_rate * $hours) : 0;
        $service = $package ? (int) $package->base_price : 0;

        return $room + $service;
    }

    public function estimateReservationFee(int|float|null $total, int|float|null $percent): int
    {
        $total = max(0, (float) ($total ?? 0));
        $percent = max(0, min(BookingSetting::RESERVATION_FEE_MAX_PERCENT, (float) ($percent ?? 0)));

        return (int) round($total * ($percent / 100));
    }

    public function estimateDiscountAmount(int|float|null $total, int|float|null $percent): int
    {
        $total = max(0, (float) ($total ?? 0));
        $percent = max(0, min(BookingSetting::SPECIAL_DISCOUNT_MAX_PERCENT, (float) ($percent ?? 0)));

        return (int) round($total * ($percent / 100));
    }

    /**
     * @return array{estimated_price: int, special_discount_percent: float, special_discount_amount: int, reservation_fee_percent: float, reservation_fee_amount: int}
     */
    private function pricingPayload(
        ?Classroom $classroom,
        ?ServicePackage $package,
        CarbonImmutable $startsAt,
        CarbonImmutable $endsAt,
        ?float $reservationFeePercent = null,
        ?float $specialDiscountPercent = null,
    ): array {
        $basePrice = $this->estimatePrice($classroom, $package, $startsAt, $endsAt);
        $discountPercent = $specialDiscountPercent ?? BookingSetting::specialDiscountPercent();
        $discountAmount = $this->estimateDiscountAmount($basePrice, $discountPercent);
        $estimatedPrice = max(0, $basePrice - $discountAmount);
        $feePercent = $reservationFeePercent ?? BookingSetting::reservationFeePercent();

        return [
            'estimated_price' => $estimatedPrice,
            'special_discount_percent' => $discountPercent,
            'special_discount_amount' => $discountAmount,
            'reservation_fee_percent' => $feePercent,
            'reservation_fee_amount' => $this->estimateReservationFee($estimatedPrice, $feePercent),
        ];
    }

    /**
     * Create a booking with conflict + capacity safety under a row-level lock.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function createBooking(array $attributes, ?int $userId = null): Booking
    {
        $startsAt = $attributes['starts_at'];
        $endsAt   = $attributes['ends_at'];

        return DB::transaction(function () use ($attributes, $userId, $startsAt, $endsAt) {
            $classroom = isset($attributes['classroom_id'])
                ? Classroom::lockForUpdate()->find($attributes['classroom_id'])
                : null;

            $package = isset($attributes['service_package_id'])
                ? ServicePackage::find($attributes['service_package_id'])
                : null;

            if ($classroom && $this->hasExactDuplicateBooking($attributes, $userId)) {
                throw ValidationException::withMessages([
                    'booking_date' => 'This exact booking request already exists.',
                ]);
            }

            if ($classroom && ! $this->isClassroomAvailable($classroom->id, $startsAt, $endsAt)) {
                throw ValidationException::withMessages([
                    'classroom_id' => 'That room is already booked for the selected time block.',
                ]);
            }

            $hasAdditionalDailyBooking = $classroom && $this->hasAdditionalDailyBooking($attributes, $userId);

            if ($hasAdditionalDailyBooking && empty($attributes['confirm_additional_booking'])) {
                throw ValidationException::withMessages([
                    'confirm_additional_booking' => 'You already have an active booking for this room on this date. Confirm that you want to request another time slot.',
                ]);
            }

            if ($classroom && ! empty($attributes['participant_count']) && $attributes['participant_count'] > $classroom->capacity) {
                throw ValidationException::withMessages([
                    'participant_count' => "This room holds up to {$classroom->capacity} participants.",
                ]);
            }

            $pricing = $this->pricingPayload($classroom, $package, $startsAt, $endsAt);

            $booking = Booking::create([
                ...collect($attributes)->except([
                    'booking_end_date',
                    'confirm_additional_booking',
                    'time_block',
                    'website',
                ])->all(),
                'user_id'         => $userId,
                'status'          => self::STATUS_PENDING,
                'payment_scope'   => $attributes['payment_scope'] ?? Booking::PAYMENT_SCOPE_RESERVATION,
                ...$pricing,
            ]);

            if ($hasAdditionalDailyBooking) {
                $this->recordSpecialCase(
                    booking: $booking,
                    type: BookingSpecialCase::TYPE_ADDITIONAL_SAME_DAY_BOOKING,
                    message: 'Customer already has another active booking for this room on the same date.',
                    details: 'Allowed only after customer confirmation.',
                    severity: BookingSpecialCase::SEVERITY_WARNING,
                    userId: $userId,
                );
            }

            return $booking;
        });
    }

    /**
     * Create one pending booking per day in a date range, using the same daily time block.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<int, Booking>
     */
    public function createDailyBookings(array $attributes, ?int $userId = null): array
    {
        $dates = $this->dailyDatesForRange(
            (string) $attributes['booking_date'],
            $attributes['booking_end_date'] ?? null,
        );

        return DB::transaction(function () use ($attributes, $userId, $dates) {
            $bookings = [];

            foreach ($dates as $date) {
                [$startsAt, $endsAt] = $this->resolveTimeBlock($date, (string) $attributes['time_block']);

                $bookings[] = $this->createBooking(
                    attributes: [
                        ...$attributes,
                        'booking_date' => $date,
                        'starts_at' => $startsAt,
                        'ends_at' => $endsAt,
                    ],
                    userId: $userId,
                );
            }

            return $bookings;
        });
    }

    /**
     * Move an existing booking to a different room/time block.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function rescheduleBooking(Booking $booking, array $attributes, bool $requiresReview = false): Booking
    {
        $startsAt = $attributes['starts_at'];
        $endsAt = $attributes['ends_at'];

        return DB::transaction(function () use ($booking, $attributes, $requiresReview, $startsAt, $endsAt) {
            $classroom = Classroom::lockForUpdate()->findOrFail($attributes['classroom_id']);
            $package = $booking->servicePackage;

            if (! $this->isClassroomAvailable($classroom->id, $startsAt, $endsAt, $booking->id)) {
                throw ValidationException::withMessages([
                    'classroom_id' => 'That room is already booked for the selected time block.',
                ]);
            }

            if ($booking->participant_count && $booking->participant_count > $classroom->capacity) {
                throw ValidationException::withMessages([
                    'classroom_id' => "This room holds up to {$classroom->capacity} participants.",
                ]);
            }

            $reservationFeePercent = $booking->reservation_fee_percent ?? BookingSetting::reservationFeePercent();
            $specialDiscountPercent = $booking->special_discount_percent ?? BookingSetting::specialDiscountPercent();
            $pricing = $this->pricingPayload($classroom, $package, $startsAt, $endsAt, (float) $reservationFeePercent, (float) $specialDiscountPercent);

            $updates = [
                'classroom_id' => $classroom->id,
                'booking_date' => $attributes['booking_date'],
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                ...$pricing,
                'reminder_sent_at' => null,
            ];

            if ($requiresReview) {
                $updates = [
                    ...$updates,
                    'status' => self::STATUS_PENDING,
                    'approved_by' => null,
                    'approved_at' => null,
                    'rejected_at' => null,
                    'cancelled_at' => null,
                    'cancelled_by' => null,
                    'cancellation_reason' => null,
                ];
            }

            if (! empty($attributes['reschedule_note'])) {
                $updates['customer_notes'] = trim(implode("\n\n", array_filter([
                    $booking->customer_notes,
                    'Reschedule note: '.$attributes['reschedule_note'],
                ])));
            }

            $booking->update($updates);

            if ($this->hasAdditionalDailyBooking([...$attributes, 'contact' => $booking->contact], $booking->user_id, $booking->id)) {
                $this->recordSpecialCase(
                    booking: $booking,
                    type: BookingSpecialCase::TYPE_ADDITIONAL_SAME_DAY_BOOKING,
                    message: 'Customer has another active booking for this room on the same date.',
                    details: 'Created during reschedule review.',
                    severity: BookingSpecialCase::SEVERITY_WARNING,
                    userId: $booking->user_id,
                );
            }

            return $booking->fresh(['classroom', 'servicePackage']);
        });
    }

    public function cancelBooking(Booking $booking, ?int $userId = null, ?string $reason = null): Booking
    {
        return DB::transaction(function () use ($booking, $userId, $reason) {
            $booking->markCancelled($userId, $reason ? trim($reason) : null);
            $booking->refresh();

            if ($booking->wasCancelledOnScheduledDay()) {
                $this->recordSpecialCase(
                    booking: $booking,
                    type: BookingSpecialCase::TYPE_SAME_DAY_CANCELLATION,
                    message: 'Booking was cancelled on the scheduled date.',
                    details: $booking->cancellation_reason,
                    severity: BookingSpecialCase::SEVERITY_WARNING,
                    userId: $userId,
                );
            } elseif ($booking->starts_at && $booking->starts_at->isFuture() && $booking->starts_at->lte(now()->addDay())) {
                $this->recordSpecialCase(
                    booking: $booking,
                    type: BookingSpecialCase::TYPE_LATE_CANCELLATION,
                    message: 'Booking was cancelled less than 24 hours before the scheduled start.',
                    details: $booking->cancellation_reason,
                    severity: BookingSpecialCase::SEVERITY_WARNING,
                    userId: $userId,
                );
            }

            return $booking->fresh(['classroom', 'servicePackage', 'specialCases']);
        });
    }

    public function applyPricingSettingsToUnpaidActiveBookings(float $reservationFeePercent, float $specialDiscountPercent): int
    {
        $updated = 0;

        Booking::query()
            ->with(['classroom', 'servicePackage'])
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->whereNull('reservation_fee_paid_at')
            ->orderBy('id')
            ->chunkById(100, function ($bookings) use ($reservationFeePercent, $specialDiscountPercent, &$updated) {
                foreach ($bookings as $booking) {
                    if (! $booking->starts_at || ! $booking->ends_at) {
                        continue;
                    }

                    $booking->update($this->pricingPayload(
                        $booking->classroom,
                        $booking->servicePackage,
                        CarbonImmutable::parse($booking->starts_at),
                        CarbonImmutable::parse($booking->ends_at),
                        $reservationFeePercent,
                        $specialDiscountPercent,
                    ));

                    $updated++;
                }
            });

        return $updated;
    }

    private function recordSpecialCase(
        Booking $booking,
        string $type,
        string $message,
        ?string $details = null,
        string $severity = BookingSpecialCase::SEVERITY_INFO,
        ?int $userId = null,
    ): BookingSpecialCase {
        $existing = $booking->specialCases()
            ->where('type', $type)
            ->whereNull('resolved_at')
            ->first();

        if ($existing) {
            return $existing;
        }

        return $booking->specialCases()->create([
            'type' => $type,
            'severity' => $severity,
            'message' => $message,
            'details' => $details ? strip_tags($details) : null,
            'created_by' => $userId,
        ]);
    }
}
