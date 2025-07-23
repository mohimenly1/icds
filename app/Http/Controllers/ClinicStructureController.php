<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use App\Models\Room;
use Inertia\Inertia;

class ClinicStructureController extends Controller
{
    /**
     * Show the floors and rooms management page.
     */
    public function index()
    {
        return Inertia::render('ClinicStructure/Index', [
            'floors' => Floor::with('rooms')->orderBy('level')->get(),
            'allRooms' => Room::with('doctors', 'floor')->get(),
        ]);
    }
}