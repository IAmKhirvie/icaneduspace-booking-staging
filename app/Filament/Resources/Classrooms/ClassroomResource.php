<?php

namespace App\Filament\Resources\Classrooms;

use App\Filament\Resources\Classrooms\Pages\CreateClassroom;
use App\Filament\Resources\Classrooms\Pages\EditClassroom;
use App\Filament\Resources\Classrooms\Pages\ListClassrooms;
use App\Filament\Resources\Classrooms\Schemas\ClassroomForm;
use App\Filament\Resources\Classrooms\Tables\ClassroomsTable;
use App\Models\Classroom;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClassroomResource extends Resource
{
    protected static ?string $model = Classroom::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    public static function form(Schema $schema): Schema
    {
        return ClassroomForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClassroomsTable::configure($table);
    }

    public static function normalizeMediaUploadData(array $data): array
    {
        $heroUpload = self::firstMediaPath($data['hero_image_upload'] ?? null);

        if ($heroUpload) {
            $data['image_url'] = Classroom::publicImageUrl($heroUpload);
        }

        $gallery = collect($data['gallery'] ?? [])
            ->map(fn (mixed $item): ?string => self::firstMediaPath($item))
            ->map(fn (?string $path): ?string => Classroom::publicImageUrl($path))
            ->filter();

        $galleryUploads = collect($data['gallery_uploads'] ?? [])
            ->map(fn (mixed $item): ?string => self::firstMediaPath($item))
            ->map(fn (?string $path): ?string => Classroom::publicImageUrl($path))
            ->filter();

        $mergedGallery = $gallery
            ->merge($galleryUploads)
            ->unique()
            ->values()
            ->all();

        $data['gallery'] = $mergedGallery ?: null;
        unset($data['hero_image_upload'], $data['gallery_uploads']);

        return $data;
    }

    private static function firstMediaPath(mixed $value): ?string
    {
        if (is_string($value)) {
            return trim($value) ?: null;
        }

        if (! is_array($value)) {
            return null;
        }

        foreach (['url', 'path'] as $key) {
            if (isset($value[$key]) && is_string($value[$key]) && trim($value[$key]) !== '') {
                return trim($value[$key]);
            }
        }

        foreach ($value as $item) {
            $path = self::firstMediaPath($item);

            if ($path) {
                return $path;
            }
        }

        return null;
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
            'index' => ListClassrooms::route('/'),
            'create' => CreateClassroom::route('/create'),
            'edit' => EditClassroom::route('/{record}/edit'),
        ];
    }
}
