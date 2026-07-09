<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'location'    => $this->location,
            'room_number' => $this->room_number,
            'floor'       => $this->floor,
            'address'     => $this->address,
            'arrival_instructions' => $this->arrival_instructions,
            'capacity'    => $this->capacity,
            'hourly_rate' => $this->hourly_rate,
            'description' => $this->description,
            'is_active'   => $this->is_active,
        ];
    }
}
