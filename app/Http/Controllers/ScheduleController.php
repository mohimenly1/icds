<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    /**
     * Show the schedules management page.
     */
    public function index()
    {
        return Inertia::render('Schedules/Index', [
            'doctors' => Doctor::orderBy('name')->get(),
            // Eager load schedules with doctors
            'schedules' => Schedule::with('doctor')->get(),
        ]);
    }

    /**
     * Store or update the weekly schedule for a doctor.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'days' => 'required|array|size:7',
            'days.*.active' => 'required|boolean',
            'days.*.day_of_week' => 'required|integer|between:0,6',
            'days.*.start_time' => 'nullable|required_if:days.*.active,true|date_format:H:i',
            'days.*.end_time' => 'nullable|required_if:days.*.active,true|date_format:H:i|after:days.*.start_time',
        ]);

        foreach ($validated['days'] as $day) {
            if ($day['active']) {
                // If the day is active, create or update the schedule
                Schedule::updateOrCreate(
                    [
                        'doctor_id' => $validated['doctor_id'],
                        'day_of_week' => $day['day_of_week'],
                    ],
                    [
                        'start_time' => $day['start_time'],
                        'end_time' => $day['end_time'],
                    ]
                );
            } else {
                // If the day is inactive, delete the schedule entry if it exists
                Schedule::where('doctor_id', $validated['doctor_id'])
                        ->where('day_of_week', $day['day_of_week'])
                        ->delete();
            }
        }

        return Redirect::back()->with('success', 'تم تحديث جدول الطبيب بنجاح.');
    }
}
