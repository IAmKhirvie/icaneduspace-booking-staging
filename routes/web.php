<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\SettingsController;
use App\Services\SystemStatusService;
use App\Models\Classroom;
use App\Models\ServicePackage;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn (SystemStatusService $status) => response()->json($status->publicHealth()))
    ->name('health');

Route::get('/', function () {
    return view('home', [
        'classrooms' => Classroom::where('is_active', true)->orderBy('name')->get(),
        'packages'   => ServicePackage::where('is_active', true)->orderBy('base_price')->get(),
    ]);
})->name('home');

Route::get('/locale/{lang}', function (string $lang) {
    if (in_array($lang, ['en', 'ko'], true)) {
        session()->put('locale', $lang);
    }
    return back();
})->name('locale.switch');

Route::get('/rooms', [ClassroomController::class, 'index'])->name('rooms.index');
Route::get('/rooms/{classroom:slug}', [ClassroomController::class, 'show'])->name('rooms.show');

Route::get('/request-room', function () {
    if (auth()->check()) {
        return redirect()->route('bookings.create');
    }

    session()->put('url.intended', route('bookings.create'));

    return redirect()->route('register');
})->name('bookings.request');

Route::post('/bookings', [BookingController::class, 'store'])
    ->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'throttle:bookings-public'])
    ->name('bookings.store');

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', fn () => view('auth.login', ['portal' => 'admin']))
        ->name('admin.login');

    Route::get('/staff/login', fn () => view('auth.login', ['portal' => 'staff']))
        ->name('staff.login');
});

/* ============================================================
 |  Single dashboard view, three role-specific URLs:
 |    admin → /admin/dashboard
 |    staff → /staff/dashboard
 |    customer → /dashboard
 |  All three render the same Blade view with the same design.
 |  ========================================================== */

$dashboardData = function () {
    $userId = auth()->id();

    return [
        'upcoming' => \App\Models\Booking::with('classroom')
            ->where('user_id', $userId)
            ->whereIn('status', [\App\Services\BookingService::STATUS_PENDING, \App\Services\BookingService::STATUS_APPROVED])
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->take(3)
            ->get(),
        'counts' => [
            'pending'  => \App\Models\Booking::where('user_id', $userId)->where('status', 'pending')->count(),
            'approved' => \App\Models\Booking::where('user_id', $userId)->where('status', 'approved')->count(),
            'past'     => \App\Models\Booking::where('user_id', $userId)->where('ends_at', '<', now())->count(),
        ],
        'rooms' => \App\Models\Classroom::where('is_active', true)->orderBy('name')->take(3)->get(),
    ];
};

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () use ($dashboardData) {

    // Smart customer entry — routes admins/staff to their own URL, customers stay here.
    Route::get('/dashboard', function () use ($dashboardData) {
        $u = auth()->user();
        if ($u->hasAnyRole(['admin', 'super_admin'])) return redirect('/admin/dashboard');
        if ($u->hasRole('staff'))                    return redirect('/staff/dashboard');
        return view('dashboard', $dashboardData());
    })->name('dashboard');

    // Admin landing
    Route::get('/admin/dashboard', fn () => view('dashboard', $dashboardData()))
        ->middleware('role:admin')
        ->name('admin.dashboard');

    // Staff landing
    Route::get('/staff/dashboard', fn () => view('dashboard', $dashboardData()))
        ->middleware('role:staff')
        ->name('staff.dashboard');

    // Bare /admin and /staff redirect to the dashboard
    Route::get('/admin', fn () => redirect('/admin/dashboard'));
    Route::get('/staff', fn () => redirect('/staff/dashboard'));

    // Compatibility redirects for older Filament-style admin URLs.
    Route::middleware('staff')->group(function () {
        Route::get('/admin/bookings', fn () => redirect()->route('manage.bookings.index'));
        Route::get('/admin/bookings/create', fn () => redirect()->route('bookings.create'));
    });

    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::get('/bookings/{booking}/payment', [BookingController::class, 'editPayment'])->name('bookings.payment.edit');
    Route::post('/bookings/{booking}/payment', [BookingController::class, 'updatePayment'])->name('bookings.payment.update');
    Route::get('/bookings/{booking}/receipt', [BookingController::class, 'receipt'])->name('bookings.receipt.show');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings/{booking}/reschedule', [BookingController::class, 'editSchedule'])->name('bookings.reschedule.edit');
    Route::post('/bookings/{booking}/reschedule', [BookingController::class, 'reschedule'])->name('bookings.reschedule');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::get('/dashboard/search/suggestions', [GlobalSearchController::class, 'suggestions'])->name('search.suggestions');
    Route::get('/dashboard/search', GlobalSearchController::class)->name('search.index');
    Route::get('/settings', [SettingsController::class, 'show'])->name('settings');

    /* --- Bookings + calendar — staff + admin --- */
    Route::middleware('staff')->prefix('dashboard/manage')->name('manage.')->group(function () {
        Route::get ('/bookings',                      [\App\Http\Controllers\ManageController::class, 'bookings'])->name('bookings.index');
        Route::post('/bookings/{booking}/approve',    [\App\Http\Controllers\ManageController::class, 'bookingApprove'])->name('bookings.approve');
        Route::post('/bookings/{booking}/reject',     [\App\Http\Controllers\ManageController::class, 'bookingReject'])->name('bookings.reject');
        Route::post('/bookings/{booking}/cancel',     [\App\Http\Controllers\ManageController::class, 'bookingCancel'])->name('bookings.cancel');
        Route::post('/bookings/{booking}/reservation-fee/paid', [\App\Http\Controllers\ManageController::class, 'bookingReservationFeePaid'])->name('bookings.reservation-fee.paid');
        Route::post('/bookings/{booking}/reservation-fee/unpaid', [\App\Http\Controllers\ManageController::class, 'bookingReservationFeeUnpaid'])->name('bookings.reservation-fee.unpaid');
        Route::post('/bookings/{booking}/full-payment/paid', [\App\Http\Controllers\ManageController::class, 'bookingFullPaymentPaid'])->name('bookings.full-payment.paid');
        Route::post('/bookings/{booking}/full-payment/unpaid', [\App\Http\Controllers\ManageController::class, 'bookingFullPaymentUnpaid'])->name('bookings.full-payment.unpaid');

        Route::get ('/calendar',                      [\App\Http\Controllers\ManageController::class, 'calendar'])->name('calendar');
        Route::get ('/calendar/events',               [\App\Http\Controllers\ManageController::class, 'calendarEvents'])->name('calendar.events');
        Route::get ('/notifications',                 [\App\Http\Controllers\ManageController::class, 'notifications'])->name('notifications.index');
        Route::post('/notifications/read-all',         [\App\Http\Controllers\ManageController::class, 'notificationsReadAll'])->name('notifications.read-all');
        Route::post('/notifications/{notification}/read', [\App\Http\Controllers\ManageController::class, 'notificationRead'])->name('notifications.read');
        Route::get ('/status',                        [\App\Http\Controllers\ManageController::class, 'status'])->name('status');
    });

    /* --- Room/package/user management — admin only --- */
    Route::middleware('role:admin')->prefix('dashboard/manage')->name('manage.')->group(function () {
        Route::post('/bookings/settings',             [\App\Http\Controllers\ManageController::class, 'bookingSettingsSave'])->name('bookings.settings');

        Route::get ('/classrooms',                    [\App\Http\Controllers\ManageController::class, 'classrooms'])->name('classrooms.index');
        Route::get ('/classrooms/create',             [\App\Http\Controllers\ManageController::class, 'classroomCreate'])->name('classrooms.create');
        Route::post('/classrooms',                    [\App\Http\Controllers\ManageController::class, 'classroomSave'])->name('classrooms.store');
        Route::get ('/classrooms/{classroom}/edit',   [\App\Http\Controllers\ManageController::class, 'classroomEdit'])->name('classrooms.edit');
        Route::post('/classrooms/{classroom}',        [\App\Http\Controllers\ManageController::class, 'classroomSave'])->name('classrooms.update');
        Route::post('/classrooms/{classroom}/delete', [\App\Http\Controllers\ManageController::class, 'classroomDestroy'])->name('classrooms.destroy');

        Route::get ('/packages',                      [\App\Http\Controllers\ManageController::class, 'packages'])->name('packages.index');
        Route::get ('/packages/create',               [\App\Http\Controllers\ManageController::class, 'packageCreate'])->name('packages.create');
        Route::post('/packages',                      [\App\Http\Controllers\ManageController::class, 'packageSave'])->name('packages.store');
        Route::get ('/packages/{package}/edit',       [\App\Http\Controllers\ManageController::class, 'packageEdit'])->name('packages.edit');
        Route::post('/packages/{package}',            [\App\Http\Controllers\ManageController::class, 'packageSave'])->name('packages.update');
        Route::post('/packages/{package}/delete',     [\App\Http\Controllers\ManageController::class, 'packageDestroy'])->name('packages.destroy');

        Route::get ('/users',                         [\App\Http\Controllers\ManageController::class, 'users'])->name('users.index');
        Route::get ('/users/create',                  [\App\Http\Controllers\ManageController::class, 'userCreate'])->name('users.create');
        Route::post('/users',                         [\App\Http\Controllers\ManageController::class, 'userSave'])->name('users.store');
        Route::get ('/users/{user}/edit',             [\App\Http\Controllers\ManageController::class, 'userEdit'])->name('users.edit');
        Route::post('/users/{user}',                  [\App\Http\Controllers\ManageController::class, 'userSave'])->name('users.update');
        Route::post('/users/{user}/delete',           [\App\Http\Controllers\ManageController::class, 'userDestroy'])->name('users.destroy');
    });
});
