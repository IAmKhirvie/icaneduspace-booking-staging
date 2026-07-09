<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServicePackageResource;
use App\Models\ServicePackage;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServicePackageController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ServicePackageResource::collection(
            ServicePackage::where('is_active', true)->orderBy('base_price')->get()
        );
    }

    public function show(ServicePackage $servicePackage): ServicePackageResource
    {
        abort_unless($servicePackage->is_active, 404);

        return new ServicePackageResource($servicePackage);
    }
}
