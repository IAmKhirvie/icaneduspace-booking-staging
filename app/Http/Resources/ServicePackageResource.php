<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServicePackageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'slug'              => $this->slug,
            'description'       => $this->description,
            'base_price'        => $this->base_price,
            'duration_minutes'  => $this->duration_minutes,
            'included_services' => $this->included_services,
            'is_active'         => $this->is_active,
        ];
    }
}
