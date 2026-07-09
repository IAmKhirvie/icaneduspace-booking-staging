<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Classrooms\ClassroomResource;
use App\Filament\Resources\ServicePackages\ServicePackageResource;
use App\Models\Booking;
use App\Models\Classroom;
use App\Services\BookingService;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?int $navigationSort = -2;

    protected static ?string $title = 'Dashboard';

    protected string $view = 'filament.pages.dashboard';

    protected function getViewData(): array
    {
        return [
            'counts' => [
                'pending' => Booking::where('status', BookingService::STATUS_PENDING)->count(),
                'approved' => Booking::where('status', BookingService::STATUS_APPROVED)->count(),
                'today' => Booking::whereDate('starts_at', today())
                    ->whereNotIn('status', [BookingService::STATUS_REJECTED, BookingService::STATUS_CANCELLED])
                    ->count(),
                'past' => Booking::where('ends_at', '<', now())->count(),
            ],
            'upcoming' => Booking::with(['classroom', 'servicePackage'])
                ->whereIn('status', [BookingService::STATUS_PENDING, BookingService::STATUS_APPROVED])
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->take(5)
                ->get(),
            'rooms' => Classroom::where('is_active', true)
                ->orderBy('name')
                ->take(3)
                ->get(),
            'links' => [
                'bookings' => BookingResource::getUrl('index'),
                'createBooking' => BookingResource::getUrl('create'),
                'rooms' => ClassroomResource::getUrl('index'),
                'packages' => ServicePackageResource::getUrl('index'),
            ],
        ];
    }
}
