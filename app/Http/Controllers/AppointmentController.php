<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    /**
     * Show the appointments management page.
     */
    public function index()
    {
        return Inertia::render('Appointments/Index', [
            // جلب المواعيد مع معلومات الطبيب المرتبط بها
            'appointments' => Appointment::with('doctor')->latest('appointment_time')->get(),
            // جلب قائمة الأطباء لاستخدامها في نموذج الإضافة/التعديل
            'doctors' => Doctor::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'patient_name' => 'required|string|max:255',
            'patient_phone' => 'nullable|string|max:255',
            'appointment_time' => 'required|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:scheduled,completed,cancelled',
        ]);

        Appointment::create($validated);

        return Redirect::back()->with('success', 'تم تسجيل الموعد بنجاح.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'patient_name' => 'required|string|max:255',
            'patient_phone' => 'nullable|string|max:255',
            'appointment_time' => 'required|date',
            'notes' => 'nullable|string',
            'status' => 'required|in:scheduled,completed,cancelled',
        ]);

        $appointment->update($validated);

        return Redirect::back()->with('success', 'تم تحديث الموعد بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return Redirect::back()->with('success', 'تم حذف الموعد بنجاح.');
    }
}
