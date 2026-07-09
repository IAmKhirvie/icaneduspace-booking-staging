<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\ServicePackage;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function index(): View
    {
        return view('rooms.index', [
            'classrooms' => Classroom::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function show(Classroom $classroom): View
    {
        abort_unless($classroom->is_active, 404);

        return view('rooms.show', [
            'classroom' => $classroom,
            'packages'  => ServicePackage::where('is_active', true)->orderBy('base_price')->get(),
        ]);
    }
}
