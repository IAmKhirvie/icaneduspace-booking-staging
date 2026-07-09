<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Models\Booking;
use App\Models\Classroom;
use App\Notifications\BookingApproved;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingRejected;
use App\Services\BookingService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification as MailNotification;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('customer_name')->label('Customer')->searchable(),
                TextColumn::make('classroom.name')->label('Room')->searchable()->sortable(),
                TextColumn::make('starts_at')->label('Start')->dateTime('M d, Y H:i')->sortable(),
                TextColumn::make('ends_at')->label('End')->dateTime('H:i')->sortable(),
                TextColumn::make('participant_count')->label('Pax')->numeric()->sortable(),
                TextColumn::make('format')->badge(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => BookingService::STATUS_LABELS[$state] ?? ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        BookingService::STATUS_PENDING   => 'warning',
                        BookingService::STATUS_APPROVED  => 'success',
                        BookingService::STATUS_REJECTED  => 'danger',
                        BookingService::STATUS_CANCELLED => 'gray',
                        BookingService::STATUS_COMPLETED => 'info',
                        default                          => 'gray',
                    }),
                TextColumn::make('estimated_price')
                    ->label('Est.')
                    ->formatStateUsing(fn ($state) => \App\Support\Money::format($state))
                    ->sortable(),
                TextColumn::make('contact')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('purpose')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        BookingService::STATUS_PENDING   => 'Pending',
                        BookingService::STATUS_APPROVED  => 'Booked',
                        BookingService::STATUS_REJECTED  => 'Rejected',
                        BookingService::STATUS_CANCELLED => 'Cancelled',
                        BookingService::STATUS_COMPLETED => 'Completed',
                    ])
                    ->default(BookingService::STATUS_PENDING),
                SelectFilter::make('classroom_id')
                    ->label('Room')
                    ->options(fn () => Classroom::pluck('name', 'id')->all()),
                Filter::make('upcoming')
                    ->label('Upcoming only')
                    ->query(fn (Builder $q) => $q->where('starts_at', '>=', now())),
            ])
            ->recordActions([
                Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Booking $r) => $r->isPending())
                    ->requiresConfirmation()
                    ->action(function (Booking $r) {
                        $r->markApproved(Auth::id());
                        if ($email = $r->contact_email_or_fallback()) {
                            MailNotification::route('mail', $email)->notify(new BookingApproved($r));
                        }
                        FilamentNotification::make()->title('Booking approved')->success()->send();
                    }),
                Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Booking $r) => $r->isPending())
                    ->schema([
                        Textarea::make('reason')->label('Reason (sent to customer)')->rows(3),
                    ])
                    ->action(function (Booking $r, array $data) {
                        $r->markRejected(Auth::id());
                        if ($email = $r->contact_email_or_fallback()) {
                            MailNotification::route('mail', $email)->notify(new BookingRejected($r, $data['reason'] ?? null));
                        }
                        FilamentNotification::make()->title('Booking rejected')->danger()->send();
                    }),
                Action::make('cancel')
                    ->icon('heroicon-o-no-symbol')
                    ->color('gray')
                    ->visible(fn (Booking $r) => ! $r->isCancelled() && ! $r->isRejected())
                    ->requiresConfirmation()
                    ->action(function (Booking $r) {
                        app(BookingService::class)->cancelBooking($r, Auth::id());
                        if ($email = $r->contact_email_or_fallback()) {
                            MailNotification::route('mail', $email)->notify(new BookingCancelled($r));
                        }
                        FilamentNotification::make()->title('Booking cancelled')->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
