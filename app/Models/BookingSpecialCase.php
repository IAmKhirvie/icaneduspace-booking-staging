<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingSpecialCase extends Model
{
    public const TYPE_ADDITIONAL_SAME_DAY_BOOKING = 'additional_same_day_booking';
    public const TYPE_SAME_DAY_CANCELLATION = 'same_day_cancellation';
    public const TYPE_LATE_CANCELLATION = 'late_cancellation';

    public const SEVERITY_INFO = 'info';
    public const SEVERITY_WARNING = 'warning';

    protected $fillable = [
        'booking_id',
        'type',
        'severity',
        'message',
        'details',
        'created_by',
        'resolved_at',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
