<?php

namespace App\Filament\Resources\ServicePackages;

use App\Filament\Resources\ServicePackages\Pages\CreateServicePackage;
use App\Filament\Resources\ServicePackages\Pages\EditServicePackage;
use App\Filament\Resources\ServicePackages\Pages\ListServicePackages;
use App\Filament\Resources\ServicePackages\Schemas\ServicePackageForm;
use App\Filament\Resources\ServicePackages\Tables\ServicePackagesTable;
use App\Models\ServicePackage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ServicePackageResource extends Resource
{
    protected static ?string $model = ServicePackage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    public static function form(Schema $schema): Schema
    {
        return ServicePackageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServicePackagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServicePackages::route('/'),
            'create' => CreateServicePackage::route('/create'),
            'edit' => EditServicePackage::route('/{record}/edit'),
        ];
    }
}
