<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ClinicDataController;
use App\Http\Controllers\AiBotController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/doctors', [DoctorController::class, 'index']);
// New route to get all clinic data for the display screen
Route::get('/clinic-data', ClinicDataController::class);
Route::post('/bot/ask', [AiBotController::class, 'ask']);