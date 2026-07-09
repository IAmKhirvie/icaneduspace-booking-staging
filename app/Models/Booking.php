<?php

namespace App\Models;

use App\Services\BookingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Booking extends Model
{
    use HasFactory;
    use LogsActivity;

    public const EQUIPMENT_OPTIONS = [
        'school_ai_tools' => 'School AI tools',
        'projector_display' => 'Projector / display',
        'school_laptop' => 'School laptop',
        'microphone_speaker' => 'Microphone / speaker',
        'whiteboard_markers' => 'Whiteboard / markers',
        'video_conference' => 'Video conference setup',
    ];

    public const SNACK_BEVERAGE_OPTIONS = [
        'water' => 'Water',
        'coffee_tea' => 'Coffee / tea',
        'light_snacks' => 'Light snacks',
        'premium_beverage' => 'Premium beverage',
    ];

    private const LEGACY_SNACK_BEVERAGE_OPTIONS = [
        'beverages' => 'Premium beverage',
        'full_refreshments' => 'Premium beverage',
    ];

    public const PAYMENT_METHOD_OPTIONS = [
        'cash' => 'Cash at counter',
    ];

    public const PAYMENT_SCOPE_RESERVATION = 'reservation';
    public const PAYMENT_SCOPE_FULL = 'full';

    public const PAYMENT_SCOPE_OPTIONS = [
        self::PAYMENT_SCOPE_RESERVATION => 'Reservation fee only',
        self::PAYMENT_SCOPE_FULL => 'Full payment',
    ];

    private const LEGACY_PAYMENT_METHOD_LABELS = [
        'card' => 'Card',
        'ecash' => 'E-cash',
        'gcash' => 'E-cash',
        'bank_transfer' => 'E-cash',
        'other' => 'To be discussed with staff',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'starts_at', 'ends_at', 'classroom_id', 'service_package_id', 'equipment_requests', 'equipment_notes', 'snack_beverage_requests', 'snack_beverage_notes', 'payment_method', 'payment_scope', 'estimated_price', 'special_discount_percent', 'special_discount_amount', 'reservation_fee_percent', 'reservation_fee_amount', 'reservation_fee_paid_at', 'reservation_fee_marked_paid_by', 'full_payment_paid_at', 'full_payment_marked_paid_by', 'approved_by', 'approved_at', 'rejected_at', 'internal_notes', 'cancelled_at', 'cancelled_by', 'cancellation_reason'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('booking');
    }

    protected $fillable = [
        'user_id',
        'classroom_id',
        'service_package_id',
        'status',
        'booking_date',
        'starts_at',
        'ends_at',
        'participant_count',
        'purpose',
        'format',
        'equipment_requests',
        'equipment_notes',
        'snack_beverage_requests',
        'snack_beverage_notes',
        'customer_name',
        'organization',
        'contact',
        'payment_method',
        'payment_scope',
        'customer_notes',
        'internal_notes',
        'estimated_price',
        'special_discount_percent',
        'special_discount_amount',
        'reservation_fee_percent',
        'reservation_fee_amount',
        'reservation_fee_paid_at',
        'reservation_fee_marked_paid_by',
        'full_payment_paid_at',
        'full_payment_marked_paid_by',
        'approved_by',
        'approved_at',
        'rejected_at',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'starts_at'    => 'datetime',
            'ends_at'      => 'datetime',
            'approved_at'  => 'datetime',
            'rejected_at'  => 'datetime',
            'cancelled_at' => 'datetime',
            'special_discount_percent' => 'decimal:2',
            'reservation_fee_percent' => 'decimal:2',
            'reservation_fee_paid_at' => 'datetime',
            'full_payment_paid_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
            'equipment_requests' => 'array',
            'snack_beverage_requests' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function servicePackage(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reservationFeeMarkedPaidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reservation_fee_marked_paid_by');
    }

    public function fullPaymentMarkedPaidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'full_payment_marked_paid_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(BookingNote::class);
    }

    public function specialCases(): HasMany
    {
        return $this->hasMany(BookingSpecialCase::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(BookingNotification::class);
    }

    public function openSpecialCases(): HasMany
    {
        return $this->specialCases()->whereNull('resolved_at');
    }

    /* ---------------- state transitions ---------------- */

    public function isPending(): bool   { return $this->status === BookingService::STATUS_PENDING; }
    public function isApproved(): bool  { return $this->status === BookingService::STATUS_APPROVED; }
    public function isCancelled(): bool { return $this->status === BookingService::STATUS_CANCELLED; }
    public function isRejected(): bool  { return $this->status === BookingService::STATUS_REJECTED; }

    public function statusLabel(): string
    {
        return BookingService::STATUS_LABELS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function workflowStage(): string
    {
        if ($this->isCancelled()) {
            return 'cancelled';
        }

        if ($this->isRejected()) {
            return 'rejected';
        }

        if ($this->status === BookingService::STATUS_COMPLETED) {
            return 'completed';
        }

        if ($this->full_payment_paid_at) {
            return 'fully_paid';
        }

        if ($this->reservation_fee_paid_at) {
            return $this->isApproved() ? 'booked_reservation_paid' : 'reservation_paid_pending_review';
        }

        if ($this->isApproved()) {
            return $this->reservation_fee_amount ? 'booked_awaiting_payment' : 'booked';
        }

        if ($this->payment_method) {
            return 'awaiting_counter_payment';
        }

        return 'pending_review';
    }

    public function workflowStageLabel(): string
    {
        return match ($this->workflowStage()) {
            'cancelled' => 'Cancelled',
            'rejected' => 'Rejected',
            'completed' => 'Completed',
            'fully_paid' => 'Fully paid',
            'booked_reservation_paid' => 'Booked · reservation paid',
            'reservation_paid_pending_review' => 'Reservation paid · pending review',
            'booked_awaiting_payment' => 'Booked · awaiting payment',
            'booked' => 'Booked',
            'awaiting_counter_payment' => 'Awaiting counter payment',
            default => 'Pending review',
        };
    }

    public function workflowBadgeClass(): string
    {
        return match ($this->workflowStage()) {
            'fully_paid', 'booked_reservation_paid', 'booked', 'completed' => 'status-approved',
            'cancelled' => 'status-cancelled',
            'rejected' => 'status-rejected',
            default => 'status-pending',
        };
    }

    /**
     * @return list<array{label: string, description: string, at: mixed, state: string}>
     */
    public function workflowTimeline(): array
    {
        $isClosed = $this->isCancelled() || $this->isRejected();
        $selectedPaymentAt = $this->selectedPaymentPaidAt();

        $timeline = [
            [
                'label' => 'Request submitted',
                'description' => 'Customer created the booking request.',
                'at' => $this->created_at,
                'state' => 'complete',
            ],
            [
                'label' => $this->isRejected() ? 'Review rejected' : ($this->isApproved() ? 'Review approved' : 'Staff review'),
                'description' => $this->isRejected()
                    ? 'Staff rejected the booking request.'
                    : ($this->isApproved() ? 'Staff booked the requested schedule.' : 'Waiting for staff to review the request.'),
                'at' => $this->rejected_at ?: $this->approved_at,
                'state' => $this->isRejected() || $this->isApproved() ? 'complete' : ($this->isPending() ? 'current' : 'pending'),
            ],
        ];

        if ($this->payment_method || $this->reservation_fee_amount || $this->estimated_price !== null) {
            $timeline[] = [
                'label' => $selectedPaymentAt ? 'Payment recorded' : ($this->payment_method ? 'Counter slip created' : 'Payment selection'),
                'description' => $selectedPaymentAt
                    ? $this->paymentScopeLabel().' recorded by staff.'
                    : ($this->payment_method ? 'Customer selected '.$this->paymentMethodLabel().'.' : 'Customer still needs a counter payment slip.'),
                'at' => $selectedPaymentAt,
                'state' => $selectedPaymentAt ? 'complete' : ($isClosed ? 'pending' : 'current'),
            ];
        }

        if ($this->reservation_fee_amount) {
            $timeline[] = [
                'label' => 'Reservation fee',
                'description' => $this->reservation_fee_paid_at ? 'Reservation fee is paid.' : 'Reservation fee still needs staff confirmation.',
                'at' => $this->reservation_fee_paid_at,
                'state' => $this->reservation_fee_paid_at ? 'complete' : ($isClosed ? 'pending' : 'current'),
            ];
        }

        if ($this->isFullPaymentSelected() || $this->full_payment_paid_at) {
            $timeline[] = [
                'label' => 'Full payment',
                'description' => $this->full_payment_paid_at ? 'Full booking amount is paid.' : 'Full payment still needs staff confirmation.',
                'at' => $this->full_payment_paid_at,
                'state' => $this->full_payment_paid_at ? 'complete' : ($isClosed ? 'pending' : 'current'),
            ];
        }

        $timeline[] = [
            'label' => $this->isCancelled() ? 'Cancelled' : 'Scheduled session',
            'description' => $this->isCancelled()
                ? ($this->cancellation_reason ?: 'Booking was cancelled.')
                : 'Room schedule for this booking.',
            'at' => $this->cancelled_at ?: $this->starts_at,
            'state' => $this->isCancelled() ? 'complete' : ($this->isApproved() ? 'current' : 'pending'),
        ];

        return $timeline;
    }

    /**
     * @return list<string>
     */
    public function workflowWarnings(): array
    {
        $warnings = [];

        if ($this->full_payment_paid_at && $this->reservation_fee_amount && ! $this->reservation_fee_paid_at) {
            $warnings[] = 'Full payment is recorded but the reservation fee timestamp is missing.';
        }

        if ($this->isApproved() && ! $this->classroom_id) {
            $warnings[] = 'Booking is marked booked without a confirmed room.';
        }

        if ($this->isCancelled() && ! $this->cancellation_reason) {
            $warnings[] = 'Booking is cancelled without a recorded reason.';
        }

        if (in_array($this->status, BookingService::ACTIVE_STATUSES, true) && $this->ends_at && $this->ends_at->isPast()) {
            $warnings[] = 'Booking schedule has passed but the booking is still active.';
        }

        return $warnings;
    }

    public function reservationFeeStatusLabel(): string
    {
        if (! $this->reservation_fee_amount) {
            return 'Not required';
        }

        return $this->reservation_fee_paid_at ? 'Paid' : 'Unpaid';
    }

    public function fullPaymentStatusLabel(): string
    {
        if ($this->estimated_price === null) {
            return 'Not available';
        }

        return $this->full_payment_paid_at ? 'Paid' : 'Unpaid';
    }

    public function paymentMethodLabel(): string
    {
        if (! $this->payment_method) {
            return 'To be confirmed';
        }

        return self::PAYMENT_METHOD_OPTIONS[$this->payment_method]
            ?? self::LEGACY_PAYMENT_METHOD_LABELS[$this->payment_method]
            ?? ucwords(str_replace(['_', '-'], ' ', $this->payment_method));
    }

    public function paymentMethodFormValue(): ?string
    {
        return match ($this->payment_method) {
            'cash' => 'cash',
            'card', 'ecash', 'gcash', 'bank_transfer' => 'cash',
            default => null,
        };
    }

    public function paymentScopeFormValue(): string
    {
        if ($this->payment_scope === self::PAYMENT_SCOPE_FULL || $this->full_payment_paid_at) {
            return self::PAYMENT_SCOPE_FULL;
        }

        return self::PAYMENT_SCOPE_RESERVATION;
    }

    public function paymentScopeLabel(): string
    {
        return self::PAYMENT_SCOPE_OPTIONS[$this->paymentScopeFormValue()];
    }

    public function isFullPaymentSelected(): bool
    {
        return $this->paymentScopeFormValue() === self::PAYMENT_SCOPE_FULL;
    }

    public function counterAmountDue(): ?int
    {
        if ($this->isFullPaymentSelected()) {
            return $this->estimated_price !== null ? (int) $this->estimated_price : null;
        }

        return $this->reservation_fee_amount !== null ? (int) $this->reservation_fee_amount : null;
    }

    public function selectedPaymentPaidAt()
    {
        return $this->isFullPaymentSelected()
            ? $this->full_payment_paid_at
            : $this->reservation_fee_paid_at;
    }

    public function selectedPaymentStatusLabel(): string
    {
        return $this->isFullPaymentSelected()
            ? $this->fullPaymentStatusLabel()
            : $this->reservationFeeStatusLabel();
    }

    public function paymentReference(): string
    {
        $date = $this->created_at?->format('Ymd') ?? now()->format('Ymd');

        return 'ICAN-'.$date.'-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function wasCancelledOnScheduledDay(): bool
    {
        return (bool) $this->cancelled_at
            && (bool) $this->starts_at
            && $this->cancelled_at->isSameDay($this->starts_at);
    }

    public function markApproved(?int $approverId = null): self
    {
        $this->update([
            'status'      => BookingService::STATUS_APPROVED,
            'approved_by' => $approverId,
            'approved_at' => now(),
            'rejected_at' => null,
            'cancelled_at' => null,
            'cancelled_by' => null,
            'cancellation_reason' => null,
        ]);

        return $this;
    }

    public function markRejected(?int $approverId = null): self
    {
        $this->update([
            'status'      => BookingService::STATUS_REJECTED,
            'approved_by' => $approverId,
            'approved_at' => null,
            'rejected_at' => now(),
            'cancelled_at' => null,
            'cancelled_by' => null,
            'cancellation_reason' => null,
        ]);

        return $this;
    }

    public function markReservationFeePaid(?int $userId = null): self
    {
        $this->update([
            'payment_method' => $this->payment_method ?: 'cash',
            'payment_scope' => $this->payment_scope ?: self::PAYMENT_SCOPE_RESERVATION,
            'reservation_fee_paid_at' => now(),
            'reservation_fee_marked_paid_by' => $userId,
        ]);

        return $this;
    }

    public function markReservationFeeUnpaid(): self
    {
        $this->update([
            'reservation_fee_paid_at' => null,
            'reservation_fee_marked_paid_by' => null,
            'full_payment_paid_at' => null,
            'full_payment_marked_paid_by' => null,
        ]);

        return $this;
    }

    public function markFullPaymentPaid(?int $userId = null): self
    {
        $paidAt = now();
        $updates = [
            'payment_method' => $this->payment_method ?: 'cash',
            'payment_scope' => self::PAYMENT_SCOPE_FULL,
            'full_payment_paid_at' => $paidAt,
            'full_payment_marked_paid_by' => $userId,
        ];

        if ($this->reservation_fee_amount && ! $this->reservation_fee_paid_at) {
            $updates['reservation_fee_paid_at'] = $paidAt;
            $updates['reservation_fee_marked_paid_by'] = $userId;
        }

        $this->update($updates);

        return $this;
    }

    public function markFullPaymentUnpaid(): self
    {
        $this->update([
            'payment_scope' => self::PAYMENT_SCOPE_FULL,
            'full_payment_paid_at' => null,
            'full_payment_marked_paid_by' => null,
        ]);

        return $this;
    }

    public function markCancelled(?int $userId = null, ?string $reason = null): self
    {
        $this->update([
            'status'       => BookingService::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancelled_by' => $userId,
            'cancellation_reason' => $reason,
        ]);

        return $this;
    }

    /* ---------------- helpers ---------------- */

    public function contact_email_or_fallback(): ?string
    {
        if (filter_var($this->contact, FILTER_VALIDATE_EMAIL)) {
            return $this->contact;
        }

        return $this->user?->email;
    }

    public function equipmentRequestLabels(): array
    {
        return collect($this->equipment_requests ?? [])
            ->map(fn (string $key) => self::EQUIPMENT_OPTIONS[$key] ?? $key)
            ->values()
            ->all();
    }

    public function snackBeverageRequestLabels(): array
    {
        return collect($this->snack_beverage_requests ?? [])
            ->map(fn (string $key) => self::SNACK_BEVERAGE_OPTIONS[$key] ?? self::LEGACY_SNACK_BEVERAGE_OPTIONS[$key] ?? $key)
            ->unique()
            ->values()
            ->all();
    }
}
