<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingNotificationService;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookings,
        private readonly BookingNotificationService $notifications,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'status' => ['nullable', 'string', Rule::in(BookingService::STATUSES)],
        ]);

        $user = $request->user();

        $query = Booking::with(['classroom', 'servicePackage'])
            ->orderByDesc('starts_at');

        if (! $user->hasAnyRole(['admin', 'staff'])) {
            $query->where('user_id', $user->id);
        }

        if ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }

        return BookingResource::collection($query->paginate(20));
    }

    public function store(StoreBookingRequest $request): BookingResource
    {
        $data = $request->validated();

        $bookings = $this->bookings->createDailyBookings($data, Auth::id());

        foreach ($bookings as $booking) {
            $this->notifications->bookingReceived($booking);
        }

        $booking = $bookings[0];

        return new BookingResource($booking->load(['classroom', 'servicePackage']));
    }

    public function show(Request $request, Booking $booking): BookingResource
    {
        $this->authorize('view', $booking);

        return new BookingResource($booking->load(['classroom', 'servicePackage']));
    }

    public function cancel(Request $request, Booking $booking): BookingResource
    {
        $this->authorize('cancel', $booking);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $booking = $this->bookings->cancelBooking($booking, $request->user()->id, $data['reason'] ?? null);

        $this->notifications->bookingCancelled($booking);

        return new BookingResource($booking->load(['classroom', 'servicePackage', 'specialCases']));
    }

    public function approve(Request $request, Booking $booking): BookingResource
    {
        $this->authorize('approve', $booking);

        $booking->markApproved($request->user()->id);

        $this->notifications->bookingApproved($booking);

        return new BookingResource($booking->fresh()->load(['classroom', 'servicePackage']));
    }

    public function reject(Request $request, Booking $booking): BookingResource
    {
        $this->authorize('reject', $booking);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $booking->markRejected($request->user()->id);

        $this->notifications->bookingRejected($booking, $data['reason'] ?? null);

        return new BookingResource($booking->fresh()->load(['classroom', 'servicePackage']));
    }
}
