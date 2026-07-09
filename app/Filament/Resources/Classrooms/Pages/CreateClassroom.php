<?php

namespace App\Filament\Resources\Classrooms\Pages;

use App\Filament\Resources\Classrooms\ClassroomResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClassroom extends CreateRecord
{
    protected static string $resource = ClassroomResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return ClassroomResource::normalizeMediaUploadData($data);
    }
}
