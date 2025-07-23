<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Floor;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource for the public display screen (API).
     */
    public function index()
    {
        $doctors = Doctor::with(['room.floor', 'schedules'])->get();
        $formattedDoctors = $doctors->map(function ($doctor) {
            return [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'specialty' => $doctor->specialty,
                'status' => $doctor->status,
                'room_number' => $doctor->room ? $doctor->room->room_number : null,
                'floor' => $doctor->room ? $doctor->room->floor->level : null,
            ];
        });
        return response()->json($formattedDoctors);
    }

    /**
     * Show the doctors management page.
     */
    public function showManagementPage()
    {
        return Inertia::render('Doctors/Index', [
            // جلب الأطباء مع علاقتهم الجديدة بالغرف
            'doctors' => Doctor::with('rooms.floor')->orderBy('name')->get(),
            'floors' => Floor::all(),
            'allRooms' => Room::orderBy('room_number')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialty' => 'required|string|max:255',
            'status' => 'required|in:available,busy,away',
            'room_ids' => 'required|array', // التأكد من أنه مصفوفة
            'room_ids.*' => 'exists:rooms,id', // التأكد من أن كل ID موجود
            'photo_url' => 'nullable|url',
            'bio' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $doctor = Doctor::create($validated);
            $doctor->rooms()->sync($validated['room_ids']);
        });

        return Redirect::back()->with('success', 'تمت إضافة الطبيب بنجاح.');
    }

    public function update(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialty' => 'required|string|max:255',
            'status' => 'required|in:available,busy,away',
            'room_ids' => 'required|array',
            'room_ids.*' => 'exists:rooms,id',
            'photo_url' => 'nullable|url',
            'bio' => 'nullable|string',
        ]);

        DB::transaction(function () use ($doctor, $validated) {
            $doctor->update($validated);
            $doctor->rooms()->sync($validated['room_ids']);
        });

        return Redirect::back()->with('success', 'تم تحديث بيانات الطبيب بنجاح.');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Doctor $doctor)
    {
        $doctor->delete();
        return Redirect::back()->with('success', 'تم حذف الطبيب بنجاح.');
    }
}
