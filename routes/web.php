<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Doctor;
use App\Models\Floor;
use App\Models\Room;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ClinicStructureController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ScreenController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\UnifiedContentController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

Route::get('/display/{screen:unique_key}', [DisplayController::class, 'show'])->name('screen.display');

Route::get('/test-audio', function () {
    $textToTest = "مرحبا بالعالم";
    Log::info("--- Starting Audio Test ---");
    try {
        $response = Http::timeout(30)->post('http://127.0.0.1:8001/synthesize', ['text' => $textToTest]);

        if ($response->successful() && !empty($response->body())) {
            Log::info("Audio test successful. Received " . strlen($response->body()) . " bytes.");
            // إذا نجح، قم بتشغيل الصوت مباشرة في المتصفح
            return response($response->body())->header('Content-Type', 'audio/wav');
        } else {
            $errorBody = $response->body();
            Log::error("Audio test failed. Status: " . $response->status() . " Body: " . $errorBody);
            return "Test Failed. Status: " . $response->status() . ". Check laravel.log.";
        }
    } catch (\Exception $e) {
        Log::error("Audio test EXCEPTION: " . $e->getMessage());
        return "Test Exception. Check laravel.log. Message: " . $e->getMessage();
    }
});

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');
Route::get('/doctors-management', [DoctorController::class, 'showManagementPage'])->name('doctors.index');

// مجموعة مسارات محمية تتطلب تسجيل الدخول
Route::middleware('auth')->group(function () {


        // Unified Content Management Routes
        Route::get('/unified-content', [UnifiedContentController::class, 'index'])->name('unified-content.index');
        Route::post('/unified-content', [UnifiedContentController::class, 'store'])->name('unified-content.store');
        Route::put('/unified-content/{unifiedContentSet}', [UnifiedContentController::class, 'update'])->name('unified-content.update');
        Route::delete('/unified-content/{unifiedContentSet}', [UnifiedContentController::class, 'destroy'])->name('unified-content.destroy');


    // مسارات إدارة الأطباء
        // User Management Routes
        Route::get('/users-management', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store'); // <-- المسار الجديد
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::post('/screens', [ScreenController::class, 'store'])->name('screens.store');
        Route::put('/screens/{screen}', [ScreenController::class, 'update'])->name('screens.update');
        Route::delete('/screens/{screen}', [ScreenController::class, 'destroy'])->name('screens.destroy');
        Route::get('/media', [MediaController::class, 'index'])->name('media.index');
        Route::post('/media', [MediaController::class, 'store'])->name('media.store');
        Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
        Route::get('/screens', [ScreenController::class, 'index'])->name('screens.index');
    Route::get('/clinic-structure', [ClinicStructureController::class, 'index'])->name('clinic-structure.index');
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');

    Route::post('/doctors', [DoctorController::class, 'store'])->name('doctors.store');
    Route::put('/doctors/{doctor}', [DoctorController::class, 'update'])->name('doctors.update');
    Route::delete('/doctors/{doctor}', [DoctorController::class, 'destroy'])->name('doctors.destroy');

    // مسارات إدارة الطوابق
    Route::post('/floors', [FloorController::class, 'store'])->name('floors.store');
    Route::put('/floors/{floor}', [FloorController::class, 'update'])->name('floors.update');
    Route::delete('/floors/{floor}', [FloorController::class, 'destroy'])->name('floors.destroy');

    // مسارات إدارة الغرف
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
});


// تحديث مسار لوحة التحكم ليمرر كل البيانات اللازمة
Route::get('dashboard', function () {
    return Inertia::render('Dashboard', [
        // حساب الإحصائيات في الـ backend وإرسالها في كائن واحد
        'stats' => [
            'totalDoctors' => Doctor::count(),
            'availableDoctors' => Doctor::where('status', 'available')->count(),
            'totalRooms' => Room::count(),
            'occupiedRooms' => Room::has('doctors')->count(), 
        ],
        // جلب آخر 5 أطباء مع علاقاتهم
        'latestDoctors' => Doctor::with('rooms')->latest()->take(5)->get(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
