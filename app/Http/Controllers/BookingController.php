<?php

namespace App\Http\Controllers;

use Carbon\CarbonImmutable;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\RescheduleBookingRequest;
use App\Models\Booking;
use App\Models\BookingSetting;
use App\Models\Classroom;
use App\Models\ServicePackage;
use App\Services\BookingService;
use App\Services\BookingNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookings,
        private readonly BookingNotificationService $notifications,
    ) {}

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $bookings = $this->bookings->createDailyBookings($data, Auth::id());

        foreach ($bookings as $booking) {
            $this->notifications->bookingReceived($booking);
        }

        $firstBooking = $bookings[0];
        $count = count($bookings);

        return back()
            ->with('booking_saved', $count > 1
                ? "{$count} daily booking requests saved. Staff will review and confirm by email."
                : 'Booking request saved. Staff will review and confirm by email.'
            )
            ->with('booking_reference', $firstBooking->id)
            ->with('booking_reference_count', $count);
    }

    public function index(): View
    {
        $bookings = Booking::query()
            ->with(['classroom', 'servicePackage'])
            ->where('user_id', Auth::id())
            ->orderByDesc('starts_at')
            ->paginate(15)
            ->withQueryString();

        return view('bookings.index', compact('bookings'));
    }

    public function create(): View
    {
        $contact = Auth::user()?->email;

        return view('bookings.create', [
            'classrooms' => Classroom::where('is_active', true)->orderBy('name')->get(),
            'packages'   => ServicePackage::where('is_active', true)->orderBy('base_price')->get(),
            'bookingSettings' => [
                'reservation_fee_percent' => BookingSetting::reservationFeePercent(),
                'special_discount_percent' => BookingSetting::specialDiscountPercent(),
                'payment_instructions' => BookingSetting::getValue(BookingSetting::PAYMENT_INSTRUCTIONS, ''),
            ],
            'existingBookingSlots' => Booking::query()
                ->whereIn('status', BookingService::ACTIVE_STATUSES)
                ->where(function ($query) use ($contact) {
                    $query->where('user_id', Auth::id());

                    if ($contact) {
                        $query->orWhereRaw('lower(contact) = ?', [mb_strtolower($contact)]);
                    }
                })
                ->get(['classroom_id', 'booking_date', 'starts_at', 'ends_at'])
                ->map(fn (Booking $booking) => [
                    'classroom_id' => (string) $booking->classroom_id,
                    'booking_date' => optional($booking->booking_date)->toDateString(),
                    'time_block' => $this->timeBlockFor($booking),
                ])
                ->filter(fn (array $slot) => filled($slot['booking_date']) && filled($slot['time_block']))
                ->values(),
        ]);
    }

    private function timeBlockFor(Booking $booking): ?string
    {
        $start = optional($booking->starts_at)->format('H:i');
        $end = optional($booking->ends_at)->format('H:i');

        foreach (BookingService::TIME_BLOCKS as $block => [$blockStart, $blockEnd]) {
            if ($start === $blockStart && $end === $blockEnd) {
                return $block;
            }
        }

        return null;
    }

    /**
     * @return array{total: int|null, base_total: int|null, discount_percent: float, discount_amount: int, room: int, package: int, room_rate: int|null, hours: float, hours_label: string}
     */
    private function pricingSummaryFor(Booking $booking): array
    {
        $startsAt = $booking->starts_at ? CarbonImmutable::parse($booking->starts_at) : null;
        $endsAt = $booking->ends_at ? CarbonImmutable::parse($booking->ends_at) : null;

        $minutes = $startsAt && $endsAt
            ? max(0, $startsAt->diffInMinutes($endsAt))
            : 0;
        $hours = $minutes / 60;

        $roomRate = $booking->classroom ? (int) $booking->classroom->hourly_rate : null;
        $room = $roomRate !== null ? (int) round($roomRate * $hours) : 0;
        $package = $booking->servicePackage ? (int) $booking->servicePackage->base_price : 0;
        $baseTotal = $startsAt && $endsAt
            ? $this->bookings->estimatePrice($booking->classroom, $booking->servicePackage, $startsAt, $endsAt)
            : null;
        $discountPercent = (float) ($booking->special_discount_percent ?? 0);
        $discountAmount = $baseTotal !== null
            ? (int) ($booking->special_discount_amount ?? $this->bookings->estimateDiscountAmount($baseTotal, $discountPercent))
            : 0;
        $discountedTotal = $baseTotal !== null ? max(0, $baseTotal - $discountAmount) : null;

        return [
            'total' => $discountedTotal ?? $booking->estimated_price,
            'base_total' => $baseTotal,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'room' => $room,
            'package' => $package,
            'room_rate' => $roomRate,
            'hours' => $hours,
            'hours_label' => rtrim(rtrim(number_format($hours, 2), '0'), '.') ?: '0',
        ];
    }

    public function show(Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking->load(['classroom', 'servicePackage', 'user', 'approver', 'canceller', 'reservationFeeMarkedPaidBy', 'fullPaymentMarkedPaidBy', 'specialCases.creator', 'notifications.user']);

        $activity = \Spatie\Activitylog\Models\Activity::where('subject_type', Booking::class)
            ->where('subject_id', $booking->id)
            ->latest()
            ->get();

        $pricing = $this->pricingSummaryFor($booking);
        $returnUrl = $this->safeReturnTarget(request())
            ?? (request()->user()?->hasAnyRole(['super_admin', 'admin', 'staff'])
                ? route('manage.bookings.index')
                : route('bookings.index'));
        $bookingSettings = [
            'payment_instructions' => BookingSetting::getValue(BookingSetting::PAYMENT_INSTRUCTIONS, ''),
            'arrival_instructions' => BookingSetting::getValue(BookingSetting::ARRIVAL_INSTRUCTIONS, ''),
        ];

        return view('bookings.show', compact('booking', 'activity', 'pricing', 'returnUrl', 'bookingSettings'));
    }

    public function editPayment(Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking->load(['classroom', 'servicePackage', 'user', 'reservationFeeMarkedPaidBy', 'fullPaymentMarkedPaidBy']);

        return view('bookings.payment', [
            'booking' => $booking,
            'pricing' => $this->pricingSummaryFor($booking),
            'paymentInstructions' => BookingSetting::getValue(BookingSetting::PAYMENT_INSTRUCTIONS, ''),
        ]);
    }

    public function receipt(Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking->load(['classroom', 'servicePackage', 'user', 'reservationFeeMarkedPaidBy', 'fullPaymentMarkedPaidBy']);

        return view('bookings.receipt', [
            'booking' => $booking,
            'pricing' => $this->pricingSummaryFor($booking),
            'paymentInstructions' => BookingSetting::getValue(BookingSetting::PAYMENT_INSTRUCTIONS, ''),
        ]);
    }

    public function updatePayment(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('payment', $booking);

        $data = $request->validate([
            'payment_method' => ['required', 'string', Rule::in(array_keys(Booking::PAYMENT_METHOD_OPTIONS))],
            'payment_scope' => ['required', 'string', Rule::in(array_keys(Booking::PAYMENT_SCOPE_OPTIONS))],
        ]);

        if ($data['payment_scope'] === Booking::PAYMENT_SCOPE_FULL && $booking->estimated_price === null) {
            return back()
                ->withErrors(['payment_scope' => 'Full payment is not available until pricing is calculated.'])
                ->withInput();
        }

        $booking->update([
            'payment_method' => $data['payment_method'],
            'payment_scope' => $data['payment_scope'],
        ]);

        return redirect()
            ->route('bookings.payment.edit', $booking)
            ->with('booking_saved', 'Counter payment slip generated. Present it to staff before paying cash.');
    }

    public function editSchedule(Booking $booking): View
    {
        $this->authorize('reschedule', $booking);

        $booking->load(['classroom', 'servicePackage']);

        return view('bookings.reschedule', [
            'booking' => $booking,
            'classrooms' => Classroom::where('is_active', true)->orderBy('name')->get(),
            'timeBlocks' => BookingService::TIME_BLOCKS,
            'currentTimeBlock' => $this->timeBlockFor($booking),
        ]);
    }

    public function reschedule(RescheduleBookingRequest $request, Booking $booking): RedirectResponse
    {
        $this->authorize('reschedule', $booking);

        $data = $request->validated();

        [$startsAt, $endsAt] = $this->bookings->resolveTimeBlock($data['booking_date'], $data['time_block']);

        $isStaff = $request->user()?->hasAnyRole(['super_admin', 'admin', 'staff']) ?? false;

        $this->bookings->rescheduleBooking(
            booking: $booking,
            attributes: [
                ...$data,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ],
            requiresReview: ! $isStaff,
        );

        if (! $isStaff) {
            $this->notifications->bookingRescheduledForStaff($booking->fresh(['classroom', 'servicePackage']));
        }

        return redirect()
            ->route('bookings.show', $booking)
            ->with('booking_saved', $isStaff
                ? 'Booking schedule updated. The customer was notified by email.'
                : 'Reschedule request saved. Staff will review and confirm by email.'
            );
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('cancel', $booking);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->bookings->cancelBooking($booking, Auth::id(), $data['reason'] ?? null);

        $this->notifications->bookingCancelled($booking);

        return back()->with('booking_saved', 'Booking cancelled.');
    }

    private function safeReturnTarget(Request $request): ?string
    {
        $target = (string) $request->query('return');

        if ($target === '') {
            return null;
        }

        $appRoot = rtrim(url('/'), '/');

        if (str_starts_with($target, $appRoot.'/')) {
            return $target;
        }

        if (str_starts_with($target, '/') && ! str_starts_with($target, '//')) {
            return $target;
        }

        return null;
    }
}
