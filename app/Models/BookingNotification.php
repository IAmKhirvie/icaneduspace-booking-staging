<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingNotification extends Model
{
    use HasFactory;

    public const AUDIENCE_CUSTOMER = 'customer';
    public const AUDIENCE_STAFF = 'staff';

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_SKIPPED = 'skipped';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'booking_id',
        'user_id',
        'audience',
        'channel',
        'notification_type',
        'recipient',
        'subject',
        'message',
        'status',
        'error',
        'data',
        'sent_at',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'sent_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
