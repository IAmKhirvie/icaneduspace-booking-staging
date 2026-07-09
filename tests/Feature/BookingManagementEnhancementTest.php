<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingNotification;
use App\Models\BookingSetting;
use App\Models\BookingSpecialCase;
use App\Models\Classroom;
use App\Models\ServicePackage;
use App\Models\User;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BookingManagementEnhancementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_booking_settings_with_reservation_fee_cap(): void
    {
        Role::create(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('manage.bookings.settings'), [
                'reservation_fee_percent' => 51,
                'special_discount_percent' => 0,
            ])
            ->assertSessionHasErrors('reservation_fee_percent');

        $this->actingAs($admin)
            ->post(route('manage.bookings.settings'), [
                'reservation_fee_percent' => 45,
                'special_discount_percent' => 12.5,
                'payment_instructions' => '<b>GCash before confirmation</b>',
                'arrival_instructions' => '<script>alert(1)</script>Use elevator to 11F.',
            ])
            ->assertRedirect();

        $this->assertSame(45.0, BookingSetting::reservationFeePercent());
        $this->assertSame(12.5, BookingSetting::specialDiscountPercent());
        $this->assertSame('GCash before confirmation', BookingSetting::getValue(BookingSetting::PAYMENT_INSTRUCTIONS));
        $this->assertSame('alert(1)Use elevator to 11F.', BookingSetting::getValue(BookingSetting::ARRIVAL_INSTRUCTIONS));
    }

    public function test_booking_settings_reprice_unpaid_active_bookings_with_discount(): void
    {
        Role::create(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $booking = $this->booking([
            'estimated_price' => 2400,
            'reservation_fee_percent' => 30,
            'reservation_fee_amount' => 720,
        ]);
        $booked = Booking::create([
            'classroom_id' => $booking->classroom_id,
            'status' => BookingService::STATUS_APPROVED,
            'booking_date' => now()->addDays(2)->toDateString(),
            'starts_at' => now()->addDays(2)->setTime(9, 0),
            'ends_at' => now()->addDays(2)->setTime(12, 0),
            'purpose' => 'Booked session',
            'format' => 'Offline',
            'customer_name' => 'Booked Customer',
            'contact' => 'booked@example.com',
            'estimated_price' => 2400,
            'reservation_fee_percent' => 30,
            'reservation_fee_amount' => 720,
        ]);

        $this->actingAs($admin)
            ->post(route('manage.bookings.settings'), [
                'reservation_fee_percent' => 20,
                'special_discount_percent' => 10,
                'payment_instructions' => 'Pay before confirmation.',
                'arrival_instructions' => 'Use elevator to 11F.',
            ])
            ->assertRedirect()
            ->assertSessionHas('flash', 'Booking settings saved. 2 unpaid active booking(s) updated.');

        $booking->refresh();
        $booked->refresh();

        $this->assertSame('20.00', $booking->reservation_fee_percent);
        $this->assertSame('10.00', $booking->special_discount_percent);
        $this->assertSame(240, $booking->special_discount_amount);
        $this->assertSame(2160, $booking->estimated_price);
        $this->assertSame(432, $booking->reservation_fee_amount);
        $this->assertSame('20.00', $booked->reservation_fee_percent);
        $this->assertSame('10.00', $booked->special_discount_percent);
        $this->assertSame(2160, $booked->estimated_price);
        $this->assertSame(432, $booked->reservation_fee_amount);
    }

    public function test_booking_creation_stores_reservation_fee_and_duplicate_special_case(): void
    {
        Notification::fake();

        BookingSetting::setValue(BookingSetting::RESERVATION_FEE_PERCENT, 25);

        $user = User::factory()->create(['email' => 'customer@example.com']);
        $room = $this->room();
        $package = ServicePackage::create([
            'name' => 'Workshop',
            'slug' => 'workshop',
            'base_price' => 1000,
            'is_active' => true,
        ]);
        $service = app(BookingService::class);
        $date = now()->addDay()->toDateString();
        [$morningStart, $morningEnd] = $service->resolveTimeBlock($date, 'morning');
        [$eveningStart, $eveningEnd] = $service->resolveTimeBlock($date, 'evening');

        Booking::create([
            'user_id' => $user->id,
            'classroom_id' => $room->id,
            'status' => BookingService::STATUS_PENDING,
            'booking_date' => $date,
            'starts_at' => $morningStart,
            'ends_at' => $morningEnd,
            'purpose' => 'Morning session',
            'format' => 'Offline',
            'customer_name' => 'Customer',
            'contact' => 'customer@example.com',
        ]);

        $booking = $service->createBooking([
            'classroom_id' => $room->id,
            'service_package_id' => $package->id,
            'booking_date' => $date,
            'starts_at' => $eveningStart,
            'ends_at' => $eveningEnd,
            'participant_count' => 8,
            'purpose' => 'Evening session',
            'format' => 'Offline',
            'customer_name' => 'Customer',
            'contact' => 'customer@example.com',
            'payment_method' => 'cash',
            'confirm_additional_booking' => true,
        ], $user->id);

        $this->assertSame('25.00', $booking->reservation_fee_percent);
        $this->assertSame(850, $booking->reservation_fee_amount);
        $this->assertSame('cash', $booking->payment_method);
        $this->assertSame(Booking::PAYMENT_SCOPE_RESERVATION, $booking->payment_scope);
        $this->assertSame('Cash at counter', $booking->paymentMethodLabel());
        $this->assertDatabaseHas('booking_special_cases', [
            'booking_id' => $booking->id,
            'type' => BookingSpecialCase::TYPE_ADDITIONAL_SAME_DAY_BOOKING,
            'severity' => BookingSpecialCase::SEVERITY_WARNING,
        ]);
    }

    public function test_booking_request_with_end_date_creates_daily_pending_bookings(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'name' => 'Range Customer',
            'email' => 'range.customer@example.com',
        ]);
        $room = $this->room();
        $startDate = now()->addDays(4)->toDateString();
        $endDate = now()->addDays(6)->toDateString();

        $this->actingAs($user)
            ->post(route('bookings.store'), [
                'classroom_id' => $room->id,
                'service_package_id' => null,
                'booking_date' => $startDate,
                'booking_end_date' => $endDate,
                'time_block' => 'morning',
                'participant_count' => 8,
                'purpose' => 'Daily range workshop',
                'format' => 'Offline',
                'customer_name' => 'Range Customer',
                'contact' => 'range.customer@example.com',
                'payment_method' => 'cash',
            ])
            ->assertRedirect()
            ->assertSessionHas('booking_reference_count', 3)
            ->assertSessionHas('booking_saved', '3 daily booking requests saved. Staff will review and confirm by email.');

        $this->assertSame(3, Booking::where('purpose', 'Daily range workshop')->count());

        foreach ([$startDate, now()->addDays(5)->toDateString(), $endDate] as $date) {
            $booking = Booking::where('user_id', $user->id)
                ->where('classroom_id', $room->id)
                ->whereDate('booking_date', $date)
                ->where('purpose', 'Daily range workshop')
                ->first();

            $this->assertNotNull($booking);
            $this->assertSame(BookingService::STATUS_PENDING, $booking->status);
            $this->assertSame($date.' 09:00:00', $booking->starts_at->format('Y-m-d H:i:s'));
            $this->assertSame($date.' 12:00:00', $booking->ends_at->format('Y-m-d H:i:s'));
        }
    }

    public function test_booking_form_renders_optional_end_date_and_schedule_confirmation(): void
    {
        $user = User::factory()->create();
        $this->room();

        $response = $this->actingAs($user)->get(route('bookings.create'));

        $response->assertOk();
        $response->assertSee('name="booking_end_date"', false);
        $response->assertSee('End date');
        $response->assertSee('(optional)');
        $response->assertSee('scheduleConfirmed', false);
        $response->assertSee('daily bookings from', false);
    }

    public function test_same_day_cancellation_records_reason_actor_and_special_case(): void
    {
        Notification::fake();
        Carbon::setTestNow(Carbon::parse('2026-07-01 09:00:00'));

        $staff = User::factory()->create();
        $room = $this->room();
        $booking = Booking::create([
            'classroom_id' => $room->id,
            'status' => BookingService::STATUS_APPROVED,
            'booking_date' => '2026-07-01',
            'starts_at' => Carbon::parse('2026-07-01 14:00:00'),
            'ends_at' => Carbon::parse('2026-07-01 17:00:00'),
            'purpose' => 'Lecture',
            'format' => 'Offline',
            'customer_name' => 'Customer',
            'contact' => 'customer@example.com',
        ]);

        app(BookingService::class)->cancelBooking($booking, $staff->id, 'Client emergency');

        $booking->refresh();
        $this->assertTrue($booking->isCancelled());
        $this->assertSame($staff->id, $booking->cancelled_by);
        $this->assertSame('Client emergency', $booking->cancellation_reason);
        $this->assertTrue($booking->wasCancelledOnScheduledDay());
        $this->assertDatabaseHas('booking_special_cases', [
            'booking_id' => $booking->id,
            'type' => BookingSpecialCase::TYPE_SAME_DAY_CANCELLATION,
        ]);

        Carbon::setTestNow();
    }

    public function test_staff_can_mark_reservation_fee_paid_and_unpaid(): void
    {
        Role::create(['name' => 'staff']);
        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $booking = $this->booking([
            'reservation_fee_amount' => 300,
            'reservation_fee_percent' => 30,
        ]);

        $this->actingAs($staff)
            ->post(route('manage.bookings.reservation-fee.paid', $booking))
            ->assertRedirect();

        $booking->refresh();
        $this->assertNotNull($booking->reservation_fee_paid_at);
        $this->assertSame($staff->id, $booking->reservation_fee_marked_paid_by);
        $this->assertSame('cash', $booking->payment_method);
        $this->assertSame(Booking::PAYMENT_SCOPE_RESERVATION, $booking->payment_scope);

        $this->actingAs($staff)
            ->post(route('manage.bookings.reservation-fee.unpaid', $booking))
            ->assertRedirect();

        $booking->refresh();
        $this->assertNull($booking->reservation_fee_paid_at);
        $this->assertNull($booking->reservation_fee_marked_paid_by);
    }

    public function test_staff_can_mark_full_payment_paid_and_unpaid(): void
    {
        Role::create(['name' => 'staff']);
        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $booking = $this->booking([
            'estimated_price' => 2200,
            'reservation_fee_amount' => 440,
            'reservation_fee_percent' => 20,
            'payment_scope' => Booking::PAYMENT_SCOPE_FULL,
        ]);

        $this->actingAs($staff)
            ->post(route('manage.bookings.full-payment.paid', $booking))
            ->assertRedirect();

        $booking->refresh();
        $this->assertSame('cash', $booking->payment_method);
        $this->assertSame(Booking::PAYMENT_SCOPE_FULL, $booking->payment_scope);
        $this->assertNotNull($booking->full_payment_paid_at);
        $this->assertSame($staff->id, $booking->full_payment_marked_paid_by);
        $this->assertNotNull($booking->reservation_fee_paid_at);
        $this->assertSame($staff->id, $booking->reservation_fee_marked_paid_by);

        $this->actingAs($staff)
            ->get(route('bookings.payment.edit', $booking))
            ->assertOk()
            ->assertSee('Paid receipt')
            ->assertSee('Fully paid')
            ->assertSee('Full amount paid')
            ->assertSee(\App\Support\Money::format(2200));

        $this->actingAs($staff)
            ->post(route('manage.bookings.full-payment.unpaid', $booking))
            ->assertRedirect();

        $booking->refresh();
        $this->assertSame(Booking::PAYMENT_SCOPE_FULL, $booking->payment_scope);
        $this->assertNull($booking->full_payment_paid_at);
        $this->assertNull($booking->full_payment_marked_paid_by);
        $this->assertNotNull($booking->reservation_fee_paid_at);
    }

    public function test_booking_management_renders_filters_sorting_and_unbracketed_pagination(): void
    {
        Role::create(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->booking([
            'customer_name' => 'Ada Customer',
            'purpose' => 'Team planning workshop',
            'customer_notes' => 'Projector, coffee, and arrival help needed.',
            'payment_method' => 'cash',
            'estimated_price' => 1000,
            'reservation_fee_amount' => 300,
            'reservation_fee_percent' => 30,
            'payment_scope' => Booking::PAYMENT_SCOPE_FULL,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('manage.bookings.index', [
                'per_page' => 10,
                'fee_status' => 'unpaid',
                'sort' => 'oldest',
            ]));

        $response->assertOk();
        $response->assertSee('Booking Management');
        $response->assertSee('Quick filters');
        $response->assertSee('Pending review');
        $response->assertSee('Awaiting payment');
        $response->assertSee('Notification issues');
        $response->assertSee('name="per_page"', false);
        $response->assertSee('<option value="10" selected', false);
        $response->assertSee('name="fee_status"', false);
        $response->assertSee('name="special_case"', false);
        $response->assertSee('name="sort"', false);
        $response->assertSee('name="special_discount_percent"', false);
        $response->assertSee('max="50"', false);
        $response->assertSee('aria-current="page">1', false);
        $response->assertDontSee('[1]');
        $response->assertSee('Ada Customer');
        $response->assertSee('Workflow');
        $response->assertSee('Awaiting counter payment');
        $response->assertSee('Why');
        $response->assertSee('Team planning workshop');
        $response->assertSee('Projector, coffee, and arrival help needed.');
        $response->assertSee('Customer, contact, purpose, notes');
        $response->assertSee('Payment');
        $response->assertSee('Cash at counter');
        $response->assertSee('Type');
        $response->assertSee('Full payment');
        $response->assertSee('Full');
        $response->assertSee('Ref');
        $response->assertSee('Total');
        $response->assertSee('class="w-full text-xs" style="min-width: 1180px; table-layout: fixed;"', false);
        $response->assertSee('<col style="width: 7%;">', false);
        $response->assertSee('action-menu-button', false);
        $response->assertSee('action-menu-panel', false);
        $response->assertSee('action-floating-tooltip', false);
        $response->assertSee('lg:hidden divide-y', false);
        $response->assertDontSee('md:hidden divide-y', false);
        $response->assertSee('@action-tooltip-show', false);
        $response->assertSee('x-data="bookingActionMenu()"', false);
        $response->assertSee(':style="panelStyle"', false);
        $response->assertSee('function bookingActionMenu()', false);
        $response->assertSee('data-tooltip="Open booking details and history."', false);
        $response->assertSee('data-tooltip="Open the printable receipt preview."', false);
        $response->assertSee('data-tooltip="Change the scheduled date or time."', false);
        $response->assertSee('data-tooltip="Approve this pending booking."', false);
        $response->assertSee('data-tooltip="Reject this booking request."', false);
        $response->assertSee('data-tooltip="Record only the reservation fee as paid."', false);
        $response->assertSee('data-tooltip="Record the full booking amount as paid at the counter."', false);
        $response->assertSee('data-tooltip="Cancel this booking and record a reason."', false);
        $response->assertDontSee('title="Cancel this booking and record a reason."', false);
        $response->assertDontSee('table-actions', false);
        $response->assertDontSee('table-action table-action-primary', false);
        $response->assertSee('width="18" height="18"', false);
        $response->assertSee('d="M4 6h16"', false);
        $response->assertDontSee('M9.594 3.94', false);
        $response->assertDontSee('class="size-4"', false);

        $this->actingAs($admin)
            ->get(route('manage.bookings.index', ['q' => 'Projector']))
            ->assertOk()
            ->assertSee('Ada Customer');
    }

    public function test_booking_detail_wraps_long_who_text(): void
    {
        Role::create(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $customer = User::factory()->create([
            'email' => 'verylongcustomeremailaddressforwrapping@example-subdomain.icanacademy.work',
        ]);

        $booking = $this->booking([
            'user_id' => $customer->id,
            'customer_name' => 'Very Long Customer Name That Should Wrap Inside The Who Card Without Overflowing',
            'contact' => 'verylongcustomeremailaddressforwrapping@example-subdomain.icanacademy.work',
            'organization' => 'A Very Long Organization Name That Should Also Wrap Correctly In The Same Who Card',
            'customer_notes' => 'Please prepare the room for a small group setup.',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('bookings.show', [
                'booking' => $booking,
                'return' => route('manage.bookings.index'),
            ]))
            ->assertOk()
            ->assertSee('Who')
            ->assertSee('wrap-anywhere', false)
            ->assertSee('verylongcustomeremailaddressforwrapping@example-subdomain.icanacademy.work')
            ->assertSee('Customer notes')
            ->assertSee('Booking workflow')
            ->assertSee('Request submitted')
            ->assertSee('Staff review')
            ->assertSee('Please prepare the room for a small group setup.');

        $content = $response->getContent();
        $this->assertLessThan(strpos($content, 'Customer notes'), strpos($content, 'Setup'));
    }

    public function test_customer_can_choose_payment_method_from_payment_page(): void
    {
        $customer = User::factory()->create();
        $booking = $this->booking([
            'user_id' => $customer->id,
            'payment_method' => null,
            'reservation_fee_amount' => 440,
            'reservation_fee_percent' => 20,
            'estimated_price' => 2200,
        ]);

        $response = $this->actingAs($customer)
            ->get(route('bookings.payment.edit', $booking))
            ->assertOk()
            ->assertSee('Counter payment')
            ->assertSee('Counter payment slip')
            ->assertSee($booking->paymentReference())
            ->assertSee('Cash at counter')
            ->assertSee('Payment amount')
            ->assertSee('Reservation fee only')
            ->assertSee('Full payment')
            ->assertSee('Payment type')
            ->assertSee('receipt-print-area', false)
            ->assertSee('@media print', false)
            ->assertSee('size: auto', false)
            ->assertSee('body > :not(.receipt-print-area)', false)
            ->assertSee('--receipt-scale: 0.9', false)
            ->assertSee('--receipt-border-width: 1.8px', false)
            ->assertSee('--receipt-print-height: 100vh', false)
            ->assertSee('--receipt-body-font: min(clamp(14pt, 4vmin, 22pt), clamp(13pt, 2.8vh, 18pt))', false)
            ->assertSee('--receipt-brand-font: min(clamp(28pt, 8vmin, 48pt), clamp(24pt, 5vh, 38pt))', false)
            ->assertSee('font-size: calc(var(--receipt-body-font) * var(--receipt-scale))', false)
            ->assertSee('padding: calc(var(--receipt-page-padding) * var(--receipt-scale))', false)
            ->assertSee('max-height: var(--receipt-print-height)', false)
            ->assertSee('@media print and (max-width: 90mm)', false)
            ->assertSee('@media print and (max-height: 220mm)', false)
            ->assertSee('--receipt-body-font: min(clamp(11pt, 3vmin, 15pt), clamp(10pt, 1.9vh, 12.5pt))', false)
            ->assertDontSee('transform: scale(var(--receipt-fit-scale))', false)
            ->assertSee('page-break-after: avoid', false)
            ->assertSee('receipt-amount-total', false)
            ->assertDontSee('card p-5 paid-amount-card', false)
            ->assertDontSee('<span class="paid-amount-label">Paid</span>', false)
            ->assertSee('Present this slip to ICAN staff and pay cash at the counter.')
            ->assertSee('Print slip')
            ->assertSee('Receipt preview')
            ->assertSee('Create counter slip');

        $this->assertSame(1, substr_count($response->getContent(), 'class="receipt-print-area"'));

        $this->actingAs($customer)
            ->post(route('bookings.payment.update', $booking), [
                'payment_method' => 'cash',
                'payment_scope' => Booking::PAYMENT_SCOPE_FULL,
            ])
            ->assertRedirect(route('bookings.payment.edit', $booking))
            ->assertSessionHas('booking_saved', 'Counter payment slip generated. Present it to staff before paying cash.');

        $booking->refresh();

        $this->assertSame('cash', $booking->payment_method);
        $this->assertSame(Booking::PAYMENT_SCOPE_FULL, $booking->payment_scope);
        $this->assertSame('Full payment', $booking->paymentScopeLabel());
    }

    public function test_receipt_preview_uses_shared_receipt_and_print_target(): void
    {
        $customer = User::factory()->create();
        $booking = $this->booking([
            'user_id' => $customer->id,
            'payment_method' => 'cash',
            'reservation_fee_amount' => 440,
            'reservation_fee_percent' => 20,
            'estimated_price' => 2200,
        ]);

        $response = $this->actingAs($customer)
            ->get(route('bookings.receipt.show', $booking))
            ->assertOk()
            ->assertSee('Receipt preview')
            ->assertSee($booking->paymentReference())
            ->assertSee('receipt-print-area', false)
            ->assertSee('receipt-preview-shell', false)
            ->assertDontSee('bookings.partials.receipt-paper', false)
            ->assertSee('Print slip');

        $this->assertSame(2, substr_count($response->getContent(), 'class="receipt-paper"'));
    }

    public function test_booking_notifications_are_recorded_for_staff_and_customer_events(): void
    {
        Notification::fake();

        Role::create(['name' => 'staff']);
        $staff = User::factory()->create(['email' => 'staff@example.com']);
        $staff->assignRole('staff');

        $customer = User::factory()->create(['email' => 'customer@example.com']);
        $booking = $this->booking([
            'user_id' => $customer->id,
            'contact' => 'customer@example.com',
        ]);

        app(\App\Services\BookingNotificationService::class)->bookingReceived($booking);

        $this->assertDatabaseHas('booking_notifications', [
            'booking_id' => $booking->id,
            'audience' => BookingNotification::AUDIENCE_CUSTOMER,
            'recipient' => 'customer@example.com',
            'status' => BookingNotification::STATUS_SENT,
        ]);

        $this->assertDatabaseHas('booking_notifications', [
            'booking_id' => $booking->id,
            'audience' => BookingNotification::AUDIENCE_STAFF,
            'recipient' => 'staff@example.com',
            'status' => BookingNotification::STATUS_SENT,
        ]);

        $this->actingAs($staff)
            ->get(route('bookings.show', $booking))
            ->assertOk()
            ->assertSee('Notification log')
            ->assertSee('BookingReceived')
            ->assertSee('BookingSubmittedToStaff');

        $this->actingAs($staff)
            ->get(route('manage.notifications.index'))
            ->assertOk()
            ->assertSee('Notifications')
            ->assertSee('Mark all read')
            ->assertSee('staff@example.com')
            ->assertSee('customer@example.com')
            ->assertSee('BookingReceived')
            ->assertSee('BookingSubmittedToStaff');

        $staffNotification = BookingNotification::where('booking_id', $booking->id)
            ->where('audience', BookingNotification::AUDIENCE_STAFF)
            ->where('recipient', 'staff@example.com')
            ->firstOrFail();

        $this->actingAs($staff)
            ->post(route('manage.notifications.read', $staffNotification), [
                'return' => route('manage.notifications.index'),
            ])
            ->assertRedirect(route('manage.notifications.index'))
            ->assertSessionHas('flash', 'Notification marked read.');

        $this->assertNotNull($staffNotification->fresh()->read_at);
    }

    public function test_paid_booking_payment_method_cannot_be_changed(): void
    {
        $customer = User::factory()->create();
        $booking = $this->booking([
            'user_id' => $customer->id,
            'payment_method' => 'cash',
            'reservation_fee_amount' => 440,
            'reservation_fee_percent' => 20,
            'reservation_fee_paid_at' => now(),
        ]);

        $this->actingAs($customer)
            ->get(route('bookings.payment.edit', $booking))
            ->assertOk()
            ->assertSee('Paid receipt')
            ->assertSee($booking->paymentReference())
            ->assertSee('paid-amount-card', false)
            ->assertSee('paid-amount-label', false)
            ->assertSee('Reservation paid')
            ->assertSee('Print receipt')
            ->assertSee('This receipt confirms payment recorded by ICAN staff.')
            ->assertDontSee('Create counter slip');

        $this->actingAs($customer)
            ->post(route('bookings.payment.update', $booking), [
                'payment_method' => 'card',
            ])
            ->assertForbidden();

        $this->assertSame('cash', $booking->fresh()->payment_method);
    }

    private function room(): Classroom
    {
        return Classroom::create([
            'name' => '11F AI Hub',
            'slug' => '11f-ai-hub',
            'location' => 'Ortigas',
            'room_number' => '1103',
            'floor' => '11F',
            'address' => 'Strata 100, Emerald Ave., Ortigas Center, Pasig City, Philippines',
            'arrival_instructions' => 'Take the elevator to 11F.',
            'capacity' => 24,
            'hourly_rate' => 800,
            'description' => 'Main room.',
            'is_active' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function booking(array $overrides = []): Booking
    {
        $room = $this->room();

        return Booking::create([
            'classroom_id' => $room->id,
            'status' => BookingService::STATUS_PENDING,
            'booking_date' => now()->addDay()->toDateString(),
            'starts_at' => now()->addDay()->setTime(9, 0),
            'ends_at' => now()->addDay()->setTime(12, 0),
            'purpose' => 'Lecture',
            'format' => 'Offline',
            'customer_name' => 'Customer',
            'contact' => 'customer@example.com',
            ...$overrides,
        ]);
    }
}
