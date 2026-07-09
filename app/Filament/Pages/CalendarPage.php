<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\BookingCalendarWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class CalendarPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDateRange;

    protected static ?string $title = 'Calendar';

    protected static ?string $navigationLabel = 'Calendar';

    protected static ?int $navigationSort = -1;

    protected string $view = 'filament.pages.calendar';

    protected function getHeaderWidgets(): array
    {
        return [
            BookingCalendarWidget::class,
        ];
    }
}
