<?php

namespace App\Filament\Resources\Classrooms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClassroomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label('')
                    ->getStateUsing(fn ($record) => $record->hero_image)
                    ->extraImgAttributes(['class' => 'object-cover'])
                    ->square()
                    ->size(64),
                TextColumn::make('name')->searchable(),
                TextColumn::make('location')->searchable()->toggleable(),
                TextColumn::make('capacity')->numeric()->sortable(),
                TextColumn::make('hourly_rate')
                    ->formatStateUsing(fn ($state) => \App\Support\Money::format($state))
                    ->sortable(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
