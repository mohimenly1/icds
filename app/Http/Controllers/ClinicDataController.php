<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Floor;

class ClinicDataController extends Controller
{
    /**
     * Fetch all necessary data for the public display screen.
     */
    public function __invoke()
    {
        $doctors = Doctor::with(['rooms.floor', 'schedules'])->orderBy('name')->get();
        $floors = Floor::with('rooms')->orderBy('level')->get();

        // Group doctors by specialty to create departments
        $departments = $doctors->groupBy('specialty')->map(function ($doctorsInDept, $specialty) {
            return [
                'name' => $specialty,
                'doctors' => $doctorsInDept->pluck('id'),
            ];
        })->values();

        return response()->json([
            'doctors' => $doctors,
            'floors' => $floors,
            'departments' => $departments,
        ]);
    }
}
