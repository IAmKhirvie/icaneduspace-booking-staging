<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Services\BookingService;
use Filament\Actions\CreateAction;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class BookingCalendarWidget extends FullCalendarWidget
{
    protected static ?int $sort = -1;

    protected int|string|array $columnSpan = 'full';

    public function fetchEvents(array $fetchInfo): array
    {
        return Booking::query()
            ->whereNotIn('status', [BookingService::STATUS_REJECTED, BookingService::STATUS_CANCELLED])
            ->whereBetween('starts_at', [$fetchInfo['start'], $fetchInfo['end']])
            ->with('classroom')
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
                'url'         => \App\Filament\Resources\Bookings\BookingResource::getUrl('edit', ['record' => $b->id]),
            ])
            ->all();
    }

    protected function headerActions(): array
    {
        return [
            CreateAction::make()
                ->label('New booking')
                ->icon('heroicon-o-plus')
                ->url(fn () => route('filament.admin.resources.bookings.create')),
        ];
    }

    public function config(): array
    {
        return [
            'initialView'    => 'timeGridWeek',
            'headerToolbar'  => [
                'left'   => 'prev,next today',
                'center' => 'title',
                'right'  => 'dayGridMonth,timeGridWeek,timeGridDay',
            ],
            'slotMinTime'  => '08:00:00',
            'slotMaxTime'  => '22:00:00',
            'nowIndicator' => true,
            'height'       => 'auto',
            'expandRows'   => true,
            'dayMaxEvents' => true,
        ];
    }
}
