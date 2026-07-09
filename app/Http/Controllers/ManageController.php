<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingNotification;
use App\Models\BookingSetting;
use App\Models\Classroom;
use App\Models\ServicePackage;
use App\Models\User;
use App\Services\SystemStatusService;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Services\BookingNotificationService;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ManageController extends Controller
{
    public function __construct()
    {
        // Whole controller is staff-only; the route group also enforces this.
    }

    /* ============ Bookings ============ */

    public function bookings(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'string', Rule::in(BookingService::STATUSES)],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'q' => ['nullable', 'string', 'max:80'],
            'fee_status' => ['nullable', 'string', Rule::in(['paid', 'unpaid', 'not_required'])],
            'special_case' => ['nullable', 'string', Rule::in(['open'])],
            'quick' => ['nullable', 'string', Rule::in(['pending', 'awaiting_payment', 'paid', 'today', 'this_week', 'cancelled', 'special_cases', 'notification_failures'])],
            'sort' => ['nullable', 'string', Rule::in(['newest', 'oldest'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 20, 25, 50, 75, 100])],
            'page' => ['nullable', 'integer', 'min:1'],
            'before' => ['nullable', 'integer', 'min:1'],
            'after' => ['nullable', 'integer', 'min:1'],
            'last' => ['nullable', 'boolean'],
        ]);

        $q = trim((string) ($filters['q'] ?? ''));
        $sort = (string) ($filters['sort'] ?? 'newest');
        $perPage = (int) ($filters['per_page'] ?? 20);
        $currentPage = max(1, (int) ($filters['page'] ?? 1));

        $baseQuery = Booking::query()->with(['classroom', 'user', 'servicePackage', 'openSpecialCases']);

        if ($status = $filters['status'] ?? null) {
            $baseQuery->where('status', $status);
        }
        if ($room = $filters['classroom_id'] ?? null) {
            $baseQuery->where('classroom_id', $room);
        }
        if (($filters['fee_status'] ?? null) === 'paid') {
            $baseQuery->whereNotNull('reservation_fee_paid_at');
        } elseif (($filters['fee_status'] ?? null) === 'unpaid') {
            $baseQuery->where('reservation_fee_amount', '>', 0)->whereNull('reservation_fee_paid_at');
        } elseif (($filters['fee_status'] ?? null) === 'not_required') {
            $baseQuery->where(function ($query) {
                $query->whereNull('reservation_fee_amount')->orWhere('reservation_fee_amount', 0);
            });
        }
        if (($filters['special_case'] ?? null) === 'open') {
            $baseQuery->whereHas('openSpecialCases');
        }
        if (($filters['quick'] ?? null) === 'pending') {
            $baseQuery->where('status', BookingService::STATUS_PENDING);
        } elseif (($filters['quick'] ?? null) === 'awaiting_payment') {
            $baseQuery
                ->whereIn('status', BookingService::ACTIVE_STATUSES)
                ->where(function ($query) {
                    $query
                        ->where(function ($reservation) {
                            $reservation->where('reservation_fee_amount', '>', 0)
                                ->whereNull('reservation_fee_paid_at');
                        })
                        ->orWhere(function ($full) {
                            $full->where('payment_scope', Booking::PAYMENT_SCOPE_FULL)
                                ->whereNull('full_payment_paid_at');
                        });
                });
        } elseif (($filters['quick'] ?? null) === 'paid') {
            $baseQuery->where(function ($query) {
                $query->whereNotNull('reservation_fee_paid_at')
                    ->orWhereNotNull('full_payment_paid_at');
            });
        } elseif (($filters['quick'] ?? null) === 'today') {
            $baseQuery->whereDate('starts_at', now()->toDateString());
        } elseif (($filters['quick'] ?? null) === 'this_week') {
            $baseQuery->whereBetween('starts_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif (($filters['quick'] ?? null) === 'cancelled') {
            $baseQuery->where('status', BookingService::STATUS_CANCELLED);
        } elseif (($filters['quick'] ?? null) === 'special_cases') {
            $baseQuery->whereHas('openSpecialCases');
        } elseif (($filters['quick'] ?? null) === 'notification_failures') {
            $baseQuery->whereHas('notifications', function ($query) {
                $query->whereIn('status', [BookingNotification::STATUS_FAILED, BookingNotification::STATUS_SKIPPED]);
            });
        }
        if ($q !== '') {
            $prefix = Str::lower($q).'%';
            $baseQuery->where(function ($w) use ($q, $prefix) {
                $id = ltrim($q, '#');

                $w->when(ctype_digit($id), fn ($n) => $n->orWhere('id', (int) $id))
                  ->orWhereRaw('lower(customer_name) like ?', [$prefix])
                  ->orWhereRaw('lower(contact) like ?', [$prefix])
                  ->orWhereRaw('lower(purpose) like ?', [$prefix])
                  ->orWhereRaw('lower(customer_notes) like ?', [$prefix]);
            });
        }

        $total = (clone $baseQuery)->count();
        $lastPage = max(1, (int) ceil(max(1, $total) / $perPage));
        $currentPage = min($currentPage, $lastPage);

        $bookingsQuery = clone $baseQuery;

        if ((bool) ($filters['last'] ?? false)) {
            $currentPage = $lastPage;
            $bookings = $bookingsQuery
                ->orderBy('id', $sort === 'oldest' ? 'desc' : 'asc')
                ->limit($perPage)
                ->get()
                ->reverse()
                ->values();
        } elseif (! empty($filters['after'])) {
            if ($sort === 'oldest') {
                $bookings = $bookingsQuery
                    ->where('id', '>', (int) $filters['after'])
                    ->orderBy('id')
                    ->limit($perPage)
                    ->get();
            } else {
                $bookings = $bookingsQuery
                    ->where('id', '>', (int) $filters['after'])
                    ->orderBy('id')
                    ->limit($perPage)
                    ->get()
                    ->reverse()
                    ->values();
            }
        } else {
            if (! empty($filters['before'])) {
                $bookingsQuery->where('id', '<', (int) $filters['before']);
            }

            if ($sort === 'oldest' && ! empty($filters['before'])) {
                $bookings = $bookingsQuery
                    ->orderByDesc('id')
                    ->limit($perPage)
                    ->get()
                    ->reverse()
                    ->values();
            } else {
                $bookings = $bookingsQuery
                    ->orderBy('id', $sort === 'oldest' ? 'asc' : 'desc')
                    ->limit($perPage)
                    ->get();
            }
        }

        $firstId = $bookings->first()?->id;
        $lastId = $bookings->last()?->id;
        $baseParams = array_filter([
            'status' => $filters['status'] ?? null,
            'classroom_id' => $filters['classroom_id'] ?? null,
            'fee_status' => $filters['fee_status'] ?? null,
            'special_case' => $filters['special_case'] ?? null,
            'quick' => $filters['quick'] ?? null,
            'sort' => $sort,
            'q' => $q !== '' ? $q : null,
            'per_page' => $perPage,
        ], fn ($value) => $value !== null && $value !== '');

        $firstUrl = route('manage.bookings.index', [...$baseParams, 'page' => 1]);
        $previousCursorKey = $sort === 'oldest' ? 'before' : 'after';
        $nextCursorKey = $sort === 'oldest' ? 'after' : 'before';
        $previousUrl = $currentPage > 1 && $firstId
            ? route('manage.bookings.index', [...$baseParams, 'page' => $currentPage - 1, $previousCursorKey => $firstId])
            : null;
        $nextUrl = $currentPage < $lastPage && $lastId
            ? route('manage.bookings.index', [...$baseParams, 'page' => $currentPage + 1, $nextCursorKey => $lastId])
            : null;
        $lastUrl = route('manage.bookings.index', [...$baseParams, 'page' => $lastPage, 'last' => 1]);

        $pageUrls = [1 => $firstUrl];

        if ($previousUrl) {
            $pageUrls[$currentPage - 1] = $previousUrl;
        }

        $pageUrls[$currentPage] = null;

        if ($nextUrl) {
            $pageUrls[$currentPage + 1] = $nextUrl;
        }

        if ($lastPage > 1) {
            $pageUrls[$lastPage] = $lastUrl;
        }

        ksort($pageUrls);

        $pagination = [
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
            'from' => $total === 0 ? 0 : (($currentPage - 1) * $perPage) + 1,
            'to' => min($total, $currentPage * $perPage),
            'first_url' => $firstUrl,
            'previous_url' => $previousUrl,
            'next_url' => $nextUrl,
            'last_url' => $lastUrl,
            'page_urls' => $pageUrls,
        ];

        return view('manage.bookings.index', [
            'bookings'   => $bookings,
            'classrooms' => Classroom::orderBy('name')->get(),
            'bookingSettings' => [
                'reservation_fee_percent' => BookingSetting::reservationFeePercent(),
                'special_discount_percent' => BookingSetting::specialDiscountPercent(),
                'payment_instructions' => BookingSetting::getValue(BookingSetting::PAYMENT_INSTRUCTIONS, ''),
                'arrival_instructions' => BookingSetting::getValue(BookingSetting::ARRIVAL_INSTRUCTIONS, ''),
            ],
            'alerts' => $this->bookingAlerts(),
            'canManageBookingSettings' => $request->user()?->hasAnyRole(['admin', 'super_admin']) ?? false,
            'filters'    => [
                'status'       => $filters['status'] ?? null,
                'classroom_id' => $filters['classroom_id'] ?? null,
                'fee_status'   => $filters['fee_status'] ?? null,
                'special_case' => $filters['special_case'] ?? null,
                'quick'        => $filters['quick'] ?? null,
                'sort'         => $sort,
                'q'            => $q,
            ],
            'perPage' => $perPage,
            'pagination' => $pagination,
            'returnUrl' => $request->fullUrl(),
        ]);
    }

    /**
     * @return list<array{label: string, count: int, url: string, tone: string, note: string}>
     */
    private function bookingAlerts(): array
    {
        $active = BookingService::ACTIVE_STATUSES;

        return [
            [
                'label' => 'Pending review',
                'count' => Booking::where('status', BookingService::STATUS_PENDING)->count(),
                'url' => route('manage.bookings.index', ['quick' => 'pending']),
                'tone' => 'status-pending',
                'note' => 'Needs staff decision',
            ],
            [
                'label' => 'Awaiting payment',
                'count' => Booking::whereIn('status', $active)
                    ->where(function ($query) {
                        $query
                            ->where(function ($reservation) {
                                $reservation->where('reservation_fee_amount', '>', 0)
                                    ->whereNull('reservation_fee_paid_at');
                            })
                            ->orWhere(function ($full) {
                                $full->where('payment_scope', Booking::PAYMENT_SCOPE_FULL)
                                    ->whereNull('full_payment_paid_at');
                            });
                    })
                    ->count(),
                'url' => route('manage.bookings.index', ['quick' => 'awaiting_payment']),
                'tone' => 'status-pending',
                'note' => 'Counter payment still open',
            ],
            [
                'label' => 'Today',
                'count' => Booking::whereIn('status', $active)
                    ->whereDate('starts_at', now()->toDateString())
                    ->count(),
                'url' => route('manage.bookings.index', ['quick' => 'today']),
                'tone' => 'status-approved',
                'note' => 'Scheduled today',
            ],
            [
                'label' => 'Open special cases',
                'count' => Booking::whereHas('openSpecialCases')->count(),
                'url' => route('manage.bookings.index', ['quick' => 'special_cases']),
                'tone' => 'status-pending',
                'note' => 'Duplicates, late cancellations, exceptions',
            ],
            [
                'label' => 'Notification issues',
                'count' => BookingNotification::whereIn('status', [BookingNotification::STATUS_FAILED, BookingNotification::STATUS_SKIPPED])->count(),
                'url' => route('manage.bookings.index', ['quick' => 'notification_failures']),
                'tone' => 'status-rejected',
                'note' => 'Failed or skipped delivery',
            ],
        ];
    }

    public function bookingApprove(Booking $booking): RedirectResponse
    {
        abort_unless($booking->isPending(), 422);
        $booking->markApproved(Auth::id());
        app(BookingNotificationService::class)->bookingApproved($booking);
        return back()->with('flash', 'Booking #'.$booking->id.' booked.');
    }

    public function bookingReject(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->isPending(), 422);
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:2000']]);
        $booking->markRejected(Auth::id());
        app(BookingNotificationService::class)->bookingRejected($booking, $data['reason'] ?? null);
        return back()->with('flash', 'Booking #'.$booking->id.' rejected.');
    }

    public function bookingCancel(Request $request, Booking $booking): RedirectResponse
    {
        abort_if($booking->isCancelled() || $booking->isRejected(), 422);
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        app(BookingService::class)->cancelBooking($booking, Auth::id(), $data['reason'] ?? null);
        app(BookingNotificationService::class)->bookingCancelled($booking);
        return back()->with('flash', 'Booking #'.$booking->id.' cancelled.');
    }

    public function bookingReservationFeePaid(Booking $booking): RedirectResponse
    {
        abort_if($booking->isCancelled() || $booking->isRejected(), 422);

        $booking->markReservationFeePaid(Auth::id());
        app(BookingNotificationService::class)->bookingUpdated($booking->fresh(['classroom', 'servicePackage']), [
            'Payment' => 'Reservation fee marked paid',
        ]);

        return back()->with('flash', 'Reservation fee marked paid for booking #'.$booking->id.'.');
    }

    public function bookingReservationFeeUnpaid(Booking $booking): RedirectResponse
    {
        abort_if($booking->isCancelled() || $booking->isRejected(), 422);

        $booking->markReservationFeeUnpaid();
        app(BookingNotificationService::class)->bookingUpdated($booking->fresh(['classroom', 'servicePackage']), [
            'Payment' => 'Reservation fee marked unpaid',
        ]);

        return back()->with('flash', 'Reservation fee marked unpaid for booking #'.$booking->id.'.');
    }

    public function bookingFullPaymentPaid(Booking $booking): RedirectResponse
    {
        abort_if($booking->isCancelled() || $booking->isRejected(), 422);

        $booking->markFullPaymentPaid(Auth::id());
        app(BookingNotificationService::class)->bookingUpdated($booking->fresh(['classroom', 'servicePackage']), [
            'Payment' => 'Full payment marked paid',
        ]);

        return back()->with('flash', 'Full payment marked paid for booking #'.$booking->id.'.');
    }

    public function bookingFullPaymentUnpaid(Booking $booking): RedirectResponse
    {
        abort_if($booking->isCancelled() || $booking->isRejected(), 422);

        $booking->markFullPaymentUnpaid();
        app(BookingNotificationService::class)->bookingUpdated($booking->fresh(['classroom', 'servicePackage']), [
            'Payment' => 'Full payment marked unpaid',
        ]);

        return back()->with('flash', 'Full payment marked unpaid for booking #'.$booking->id.'.');
    }

    public function bookingSettingsSave(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasAnyRole(['admin', 'super_admin']), 403);

        $data = $request->validate([
            'reservation_fee_percent' => ['required', 'numeric', 'min:0', 'max:'.BookingSetting::RESERVATION_FEE_MAX_PERCENT],
            'special_discount_percent' => ['required', 'numeric', 'min:0', 'max:'.BookingSetting::SPECIAL_DISCOUNT_MAX_PERCENT],
            'payment_instructions' => ['nullable', 'string', 'max:2000'],
            'arrival_instructions' => ['nullable', 'string', 'max:2000'],
        ]);

        $reservationFeePercent = round((float) $data['reservation_fee_percent'], 2);
        $specialDiscountPercent = round((float) $data['special_discount_percent'], 2);

        BookingSetting::setValue(BookingSetting::RESERVATION_FEE_PERCENT, $reservationFeePercent);
        BookingSetting::setValue(BookingSetting::SPECIAL_DISCOUNT_PERCENT, $specialDiscountPercent);
        BookingSetting::setValue(BookingSetting::PAYMENT_INSTRUCTIONS, strip_tags((string) ($data['payment_instructions'] ?? '')));
        BookingSetting::setValue(BookingSetting::ARRIVAL_INSTRUCTIONS, strip_tags((string) ($data['arrival_instructions'] ?? '')));

        $updated = app(BookingService::class)->applyPricingSettingsToUnpaidActiveBookings($reservationFeePercent, $specialDiscountPercent);

        return back()->with('flash', "Booking settings saved. {$updated} unpaid active booking(s) updated.");
    }

    /* ============ Classrooms ============ */

    public function classrooms(): View
    {
        return view('manage.classrooms.index', [
            'classrooms' => Classroom::orderBy('name')->paginate(20),
        ]);
    }

    public function classroomEdit(Classroom $classroom): View
    {
        return view('manage.classrooms.edit', compact('classroom'));
    }

    public function classroomCreate(): View
    {
        return view('manage.classrooms.edit', ['classroom' => new Classroom(['is_active' => true, 'capacity' => 12, 'hourly_rate' => 0])]);
    }

    public function classroomSave(Request $request, ?Classroom $classroom = null): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:160'],
            'slug'        => ['required', 'string', 'max:160'],
            'location'    => ['nullable', 'string', 'max:160'],
            'room_number' => ['nullable', 'string', 'max:80'],
            'floor'       => ['nullable', 'string', 'max:80'],
            'address'     => ['nullable', 'string', 'max:255'],
            'arrival_instructions' => ['nullable', 'string', 'max:2000'],
            'capacity'    => ['required', 'integer', 'min:1', 'max:500'],
            'hourly_rate' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image_url'   => ['nullable', 'string', 'max:255'],
            'hero_image_upload' => ['nullable', 'image', 'max:8192'],
            'amenities'   => ['nullable', 'string'],
            'gallery'     => ['nullable', 'string'],
            'gallery_uploads' => ['nullable', 'array'],
            'gallery_uploads.*' => ['image', 'max:8192'],
            'is_active'   => ['nullable'],
        ]);
        $data['room_number'] = strip_tags((string) ($data['room_number'] ?? ''));
        $data['floor'] = strip_tags((string) ($data['floor'] ?? ''));
        $data['arrival_instructions'] = strip_tags((string) ($data['arrival_instructions'] ?? ''));
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['amenities'] = $data['amenities'] ? array_values(array_filter(array_map('trim', preg_split('/[,\n]/', $data['amenities'])))) : null;
        $gallery = $data['gallery'] ? array_values(array_filter(array_map('trim', preg_split('/\n/', $data['gallery'])))) : [];

        if ($request->hasFile('hero_image_upload')) {
            $data['image_url'] = $this->storeRoomImage($request->file('hero_image_upload'));
        }

        foreach ($request->file('gallery_uploads', []) as $image) {
            $gallery[] = $this->storeRoomImage($image);
        }

        $data['gallery'] = $gallery ? array_values(array_unique($gallery)) : null;
        unset($data['hero_image_upload'], $data['gallery_uploads']);

        if ($classroom && $classroom->exists) {
            $classroom->update($data);
        } else {
            $classroom = Classroom::create($data);
        }

        return redirect()->route('manage.classrooms.index')->with('flash', 'Saved “'.$classroom->name.'”.');
    }

    private function storeRoomImage(UploadedFile $image): string
    {
        $path = $image->store('rooms', 'public');

        return '/storage/'.ltrim($path, '/');
    }

    public function classroomDestroy(Classroom $classroom): RedirectResponse
    {
        $classroom->delete();
        return back()->with('flash', 'Classroom deleted.');
    }

    /* ============ Service packages ============ */

    public function packages(): View
    {
        return view('manage.packages.index', [
            'packages' => ServicePackage::orderBy('base_price')->paginate(20),
        ]);
    }

    public function packageEdit(ServicePackage $package): View
    {
        return view('manage.packages.edit', compact('package'));
    }

    public function packageCreate(): View
    {
        return view('manage.packages.edit', ['package' => new ServicePackage(['is_active' => true, 'base_price' => 0])]);
    }

    public function packageSave(Request $request, ?ServicePackage $package = null): RedirectResponse
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:160'],
            'slug'              => ['required', 'string', 'max:160'],
            'description'       => ['nullable', 'string', 'max:2000'],
            'base_price'        => ['required', 'integer', 'min:0'],
            'duration_minutes'  => ['nullable', 'integer', 'min:0', 'max:1440'],
            'included_services' => ['nullable', 'string'],
            'is_active'         => ['nullable'],
        ]);
        $data['is_active']         = (bool) ($data['is_active'] ?? false);
        $data['included_services'] = $data['included_services'] ? array_values(array_filter(array_map('trim', preg_split('/[,\n]/', $data['included_services'])))) : null;

        if ($package && $package->exists) {
            $package->update($data);
        } else {
            $package = ServicePackage::create($data);
        }

        return redirect()->route('manage.packages.index')->with('flash', 'Saved “'.$package->name.'”.');
    }

    public function packageDestroy(ServicePackage $package): RedirectResponse
    {
        $package->delete();
        return back()->with('flash', 'Package deleted.');
    }

    /* ============ Calendar ============ */

    public function calendar(): View
    {
        return view('manage.calendar');
    }

    public function status(SystemStatusService $status): View
    {
        return view('manage.status', [
            'status' => $status->summary(),
        ]);
    }

    public function notifications(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'string', Rule::in([
                'unread',
                'read',
                'issues',
                BookingNotification::STATUS_PENDING,
                BookingNotification::STATUS_SENT,
                BookingNotification::STATUS_SKIPPED,
                BookingNotification::STATUS_FAILED,
            ])],
            'audience' => ['nullable', 'string', Rule::in([
                BookingNotification::AUDIENCE_CUSTOMER,
                BookingNotification::AUDIENCE_STAFF,
            ])],
            'q' => ['nullable', 'string', 'max:80'],
            'sort' => ['nullable', 'string', Rule::in(['newest', 'oldest'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 20, 25, 50, 75, 100])],
            'page' => ['nullable', 'integer', 'min:1'],
            'before' => ['nullable', 'integer', 'min:1'],
            'after' => ['nullable', 'integer', 'min:1'],
            'last' => ['nullable', 'boolean'],
        ]);

        $q = trim((string) ($filters['q'] ?? ''));
        $status = (string) ($filters['status'] ?? '');
        $audience = (string) ($filters['audience'] ?? '');
        $sort = (string) ($filters['sort'] ?? 'newest');
        $perPage = (int) ($filters['per_page'] ?? 20);
        $currentPage = max(1, (int) ($filters['page'] ?? 1));

        $baseQuery = BookingNotification::query()
            ->with(['booking.classroom', 'user'])
            ->when($audience !== '', fn ($query) => $query->where('audience', $audience))
            ->when($status === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->when($status === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->when($status === 'issues', fn ($query) => $query->whereIn('status', [BookingNotification::STATUS_FAILED, BookingNotification::STATUS_SKIPPED]))
            ->when(in_array($status, [
                BookingNotification::STATUS_PENDING,
                BookingNotification::STATUS_SENT,
                BookingNotification::STATUS_SKIPPED,
                BookingNotification::STATUS_FAILED,
            ], true), fn ($query) => $query->where('status', $status));

        if ($q !== '') {
            $prefix = Str::lower($q).'%';
            $contains = '%'.Str::lower($q).'%';
            $id = ltrim($q, '#');

            $baseQuery->where(function ($nested) use ($contains, $id, $prefix) {
                if (ctype_digit($id)) {
                    $nested
                        ->orWhere('id', (int) $id)
                        ->orWhere('booking_id', (int) $id);
                }

                $nested
                    ->orWhereRaw('lower(recipient) like ?', [$prefix])
                    ->orWhereRaw('lower(subject) like ?', [$contains])
                    ->orWhereRaw('lower(message) like ?', [$contains])
                    ->orWhereRaw('lower(notification_type) like ?', [$contains]);
            });
        }

        $total = (clone $baseQuery)->count();
        $lastPage = max(1, (int) ceil(max(1, $total) / $perPage));
        $currentPage = min($currentPage, $lastPage);

        $notificationsQuery = clone $baseQuery;

        if ((bool) ($filters['last'] ?? false)) {
            $currentPage = $lastPage;
            $notifications = $notificationsQuery
                ->orderBy('id', $sort === 'oldest' ? 'desc' : 'asc')
                ->limit($perPage)
                ->get()
                ->reverse()
                ->values();
        } elseif (! empty($filters['after'])) {
            if ($sort === 'oldest') {
                $notifications = $notificationsQuery
                    ->where('id', '>', (int) $filters['after'])
                    ->orderBy('id')
                    ->limit($perPage)
                    ->get();
            } else {
                $notifications = $notificationsQuery
                    ->where('id', '>', (int) $filters['after'])
                    ->orderBy('id')
                    ->limit($perPage)
                    ->get()
                    ->reverse()
                    ->values();
            }
        } else {
            if (! empty($filters['before'])) {
                $notificationsQuery->where('id', '<', (int) $filters['before']);
            }

            if ($sort === 'oldest' && ! empty($filters['before'])) {
                $notifications = $notificationsQuery
                    ->orderByDesc('id')
                    ->limit($perPage)
                    ->get()
                    ->reverse()
                    ->values();
            } else {
                $notifications = $notificationsQuery
                    ->orderBy('id', $sort === 'oldest' ? 'asc' : 'desc')
                    ->limit($perPage)
                    ->get();
            }
        }

        $firstId = $notifications->first()?->id;
        $lastId = $notifications->last()?->id;
        $baseParams = array_filter([
            'status' => $status !== '' ? $status : null,
            'audience' => $audience !== '' ? $audience : null,
            'sort' => $sort,
            'q' => $q !== '' ? $q : null,
            'per_page' => $perPage,
        ], fn ($value) => $value !== null && $value !== '');

        $firstUrl = route('manage.notifications.index', [...$baseParams, 'page' => 1]);
        $previousCursorKey = $sort === 'oldest' ? 'before' : 'after';
        $nextCursorKey = $sort === 'oldest' ? 'after' : 'before';
        $previousUrl = $currentPage > 1 && $firstId
            ? route('manage.notifications.index', [...$baseParams, 'page' => $currentPage - 1, $previousCursorKey => $firstId])
            : null;
        $nextUrl = $currentPage < $lastPage && $lastId
            ? route('manage.notifications.index', [...$baseParams, 'page' => $currentPage + 1, $nextCursorKey => $lastId])
            : null;
        $lastUrl = route('manage.notifications.index', [...$baseParams, 'page' => $lastPage, 'last' => 1]);

        $pageUrls = [1 => $firstUrl];

        if ($previousUrl) {
            $pageUrls[$currentPage - 1] = $previousUrl;
        }

        $pageUrls[$currentPage] = null;

        if ($nextUrl) {
            $pageUrls[$currentPage + 1] = $nextUrl;
        }

        if ($lastPage > 1) {
            $pageUrls[$lastPage] = $lastUrl;
        }

        ksort($pageUrls);

        $pagination = [
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
            'from' => $total === 0 ? 0 : (($currentPage - 1) * $perPage) + 1,
            'to' => min($total, $currentPage * $perPage),
            'first_url' => $firstUrl,
            'previous_url' => $previousUrl,
            'next_url' => $nextUrl,
            'last_url' => $lastUrl,
            'page_urls' => $pageUrls,
        ];

        return view('manage.notifications.index', [
            'notifications' => $notifications,
            'counts' => [
                'unread' => BookingNotification::whereNull('read_at')->count(),
                'issues' => BookingNotification::whereIn('status', [BookingNotification::STATUS_FAILED, BookingNotification::STATUS_SKIPPED])->count(),
                'staff' => BookingNotification::where('audience', BookingNotification::AUDIENCE_STAFF)->count(),
                'customer' => BookingNotification::where('audience', BookingNotification::AUDIENCE_CUSTOMER)->count(),
                'today' => BookingNotification::whereDate('created_at', now()->toDateString())->count(),
            ],
            'filters' => [
                'status' => $status,
                'audience' => $audience,
                'sort' => $sort,
                'q' => $q,
            ],
            'perPage' => $perPage,
            'pagination' => $pagination,
            'returnUrl' => $request->fullUrl(),
        ]);
    }

    public function notificationRead(Request $request, BookingNotification $notification): RedirectResponse
    {
        $notification->forceFill(['read_at' => now()])->save();

        return redirect($this->safeReturnTarget($request) ?? route('manage.notifications.index'))
            ->with('flash', 'Notification marked read.');
    }

    public function notificationsReadAll(): RedirectResponse
    {
        $count = BookingNotification::whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('flash', $count.' notification(s) marked read.');
    }

    /* ============ Users (admin only) ============ */

    public function users(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'role' => ['nullable', 'string', Rule::exists('roles', 'name')],
            'sort' => ['nullable', 'string', Rule::in(['newest', 'oldest'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 20, 25, 50, 75, 100])],
            'page' => ['nullable', 'integer', 'min:1'],
            'before' => ['nullable', 'integer', 'min:1'],
            'after' => ['nullable', 'integer', 'min:1'],
            'last' => ['nullable', 'boolean'],
        ]);

        $q = trim((string) ($filters['q'] ?? ''));
        $role = (string) ($filters['role'] ?? '');
        $sort = (string) ($filters['sort'] ?? 'newest');
        $perPage = (int) ($filters['per_page'] ?? 20);
        $currentPage = max(1, (int) ($filters['page'] ?? 1));

        $baseQuery = User::query()
            ->with('roles')
            ->when($q !== '', function ($query) use ($q) {
                $prefix = Str::lower($q).'%';

                $query->where(function ($nested) use ($prefix) {
                    $nested
                        ->whereRaw('lower(name) like ?', [$prefix])
                        ->orWhereRaw('lower(email) like ?', [$prefix]);
                });
            })
            ->when($role !== '', function ($query) use ($role) {
                $query->whereHas('roles', fn ($roles) => $roles->where('name', $role));
            });

        $total = (clone $baseQuery)->count();
        $lastPage = max(1, (int) ceil(max(1, $total) / $perPage));
        $currentPage = min($currentPage, $lastPage);

        $usersQuery = clone $baseQuery;

        if ((bool) ($filters['last'] ?? false)) {
            $currentPage = $lastPage;
            $users = $usersQuery
                ->orderBy('id', $sort === 'oldest' ? 'desc' : 'asc')
                ->limit($perPage)
                ->get()
                ->reverse()
                ->values();
        } elseif (! empty($filters['after'])) {
            if ($sort === 'oldest') {
                $users = $usersQuery
                    ->where('id', '>', (int) $filters['after'])
                    ->orderBy('id')
                    ->limit($perPage)
                    ->get();
            } else {
                $users = $usersQuery
                    ->where('id', '>', (int) $filters['after'])
                    ->orderBy('id')
                    ->limit($perPage)
                    ->get()
                    ->reverse()
                    ->values();
            }
        } else {
            if (! empty($filters['before'])) {
                $usersQuery->where('id', '<', (int) $filters['before']);
            }

            if ($sort === 'oldest' && ! empty($filters['before'])) {
                $users = $usersQuery
                    ->orderByDesc('id')
                    ->limit($perPage)
                    ->get()
                    ->reverse()
                    ->values();
            } else {
                $users = $usersQuery
                    ->orderBy('id', $sort === 'oldest' ? 'asc' : 'desc')
                    ->limit($perPage)
                    ->get();
            }
        }

        $firstId = $users->first()?->id;
        $lastId = $users->last()?->id;
        $baseParams = array_filter([
            'q' => $q !== '' ? $q : null,
            'role' => $role !== '' ? $role : null,
            'sort' => $sort,
            'per_page' => $perPage,
        ], fn ($value) => $value !== null && $value !== '');

        $firstUrl = route('manage.users.index', [...$baseParams, 'page' => 1]);
        $previousCursorKey = $sort === 'oldest' ? 'before' : 'after';
        $nextCursorKey = $sort === 'oldest' ? 'after' : 'before';
        $previousUrl = $currentPage > 1 && $firstId
            ? route('manage.users.index', [...$baseParams, 'page' => $currentPage - 1, $previousCursorKey => $firstId])
            : null;
        $nextUrl = $currentPage < $lastPage && $lastId
            ? route('manage.users.index', [...$baseParams, 'page' => $currentPage + 1, $nextCursorKey => $lastId])
            : null;
        $lastUrl = route('manage.users.index', [...$baseParams, 'page' => $lastPage, 'last' => 1]);

        $pageUrls = [1 => $firstUrl];

        if ($previousUrl) {
            $pageUrls[$currentPage - 1] = $previousUrl;
        }

        $pageUrls[$currentPage] = null;

        if ($nextUrl) {
            $pageUrls[$currentPage + 1] = $nextUrl;
        }

        if ($lastPage > 1) {
            $pageUrls[$lastPage] = $lastUrl;
        }

        ksort($pageUrls);

        $pagination = [
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
            'from' => $total === 0 ? 0 : (($currentPage - 1) * $perPage) + 1,
            'to' => min($total, $currentPage * $perPage),
            'first_url' => $firstUrl,
            'previous_url' => $previousUrl,
            'next_url' => $nextUrl,
            'last_url' => $lastUrl,
            'page_urls' => $pageUrls,
        ];

        return view('manage.users.index', [
            'users' => $users,
            'q'     => $q,
            'role' => $role,
            'sort' => $sort,
            'allRoles' => Role::orderBy('name')->pluck('name'),
            'perPage' => $perPage,
            'pagination' => $pagination,
            'returnUrl' => $request->fullUrl(),
        ]);
    }

    public function userCreate(Request $request): View
    {
        return view('manage.users.edit', [
            'user'      => new User(),
            'allRoles'  => Role::orderBy('name')->pluck('name'),
            'returnUrl' => $this->safeReturnTarget($request) ?? route('manage.users.index'),
        ]);
    }

    public function userEdit(Request $request, User $user): View
    {
        return view('manage.users.edit', [
            'user'      => $user,
            'allRoles'  => Role::orderBy('name')->pluck('name'),
            'returnUrl' => $this->safeReturnTarget($request) ?? route('manage.users.index'),
        ]);
    }

    public function userSave(Request $request, ?User $user = null): RedirectResponse
    {
        $isNew = ! $user || ! $user->exists;

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:160'],
            'email'    => ['required', 'email', 'max:160', $isNew ? 'unique:users,email' : 'unique:users,email,'.$user->id],
            'password' => [$isNew ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
            'roles'    => ['nullable', 'array'],
            'roles.*'  => ['string', Rule::exists('roles', 'name')],
            'return'   => ['nullable', 'string', 'max:2048'],
        ]);

        $payload = ['name' => $data['name'], 'email' => $data['email']];
        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        if ($isNew) {
            $user = User::create($payload);
        } else {
            $user->update($payload);
        }

        $user->syncRoles($data['roles'] ?? []);

        return redirect($this->safeReturnTarget($request) ?? route('manage.users.index'))
            ->with('flash', 'Saved “'.$user->name.'”.');
    }

    public function userDestroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('flash', 'You cannot delete your own account.');
        }
        $user->delete();
        return redirect($this->safeReturnTarget($request) ?? route('manage.users.index'))
            ->with('flash', 'User deleted.');
    }

    private function safeReturnTarget(Request $request): ?string
    {
        $target = (string) ($request->input('return') ?: $request->query('return'));

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

    /* ============ Calendar ============ */

    public function calendarEvents(Request $request)
    {
        $start = $request->query('start');
        $end   = $request->query('end');

        $bookings = Booking::with('classroom')
            ->whereNotIn('status', [BookingService::STATUS_REJECTED, BookingService::STATUS_CANCELLED])
            ->when($start, fn ($q) => $q->where('starts_at', '>=', $start))
            ->when($end,   fn ($q) => $q->where('ends_at',   '<=', $end))
            ->get()
            ->map(fn (Booking $b) => [
                'id'              => $b->id,
                'title'           => ($b->classroom?->name ?? 'Room').' · '.$b->customer_name,
                'start'           => $b->starts_at,
                'end'             => $b->ends_at,
                'backgroundColor' => match ($b->status) {
                    BookingService::STATUS_PENDING  => '#D9A72F',
                    BookingService::STATUS_APPROVED => '#10b981',
                    default                         => '#6b7280',
                },
                'borderColor' => 'rgba(255,255,255,0.2)',
                'url'         => route('manage.bookings.index', ['q' => '#'.$b->id]),
            ]);

        return response()->json($bookings);
    }
}
