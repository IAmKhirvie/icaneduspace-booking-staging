<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClassroomResource;
use App\Models\Classroom;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClassroomController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ClassroomResource::collection(
            Classroom::where('is_active', true)->orderBy('name')->get()
        );
    }

    public function show(Classroom $classroom): ClassroomResource
    {
        abort_unless($classroom->is_active, 404);

        return new ClassroomResource($classroom);
    }
}
