<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Services\BookingService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer')
                    ->columns(2)
                    ->schema([
                        TextInput::make('customer_name')->required(),
                        TextInput::make('contact')->required(),
                        TextInput::make('organization'),
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->label('Linked user'),
                    ]),

                Section::make('Reservation')
                    ->columns(2)
                    ->schema([
                        Select::make('classroom_id')
                            ->relationship('classroom', 'name')
                            ->searchable()
                            ->required(),
                        Select::make('service_package_id')
                            ->relationship('servicePackage', 'name')
                            ->searchable()
                            ->label('Package'),
                        DatePicker::make('booking_date'),
                        TextInput::make('participant_count')->numeric()->minValue(1),
                        DateTimePicker::make('starts_at')->required()->seconds(false),
                        DateTimePicker::make('ends_at')->required()->seconds(false),
                        TextInput::make('purpose')->columnSpanFull(),
                        TextInput::make('format'),
                        TextInput::make('estimated_price')
                            ->numeric()
                            ->prefix('₱'),
                    ]),

                Section::make('Workflow')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->required()
                            ->default(BookingService::STATUS_PENDING)
                            ->options([
                                BookingService::STATUS_PENDING   => 'Pending',
                                BookingService::STATUS_APPROVED  => 'Booked',
                                BookingService::STATUS_REJECTED  => 'Rejected',
                                BookingService::STATUS_CANCELLED => 'Cancelled',
                                BookingService::STATUS_COMPLETED => 'Completed',
                            ]),
                        Select::make('approved_by')
                            ->relationship('approver', 'name')
                            ->label('Approved by')
                            ->searchable(),
                        DateTimePicker::make('approved_at')->seconds(false),
                        DateTimePicker::make('rejected_at')->seconds(false),
                        DateTimePicker::make('cancelled_at')->seconds(false),
                    ]),

                Section::make('Notes')
                    ->schema([
                        Textarea::make('customer_notes')->rows(3)->columnSpanFull(),
                        Textarea::make('internal_notes')
                            ->label('Internal notes (not visible to customer)')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
