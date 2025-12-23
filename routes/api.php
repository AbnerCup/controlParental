<?php

use App\Http\Controllers\Api\AttendanceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceGatewayController;

Route::post('/gateway/device-events', [DeviceGatewayController::class, 'ingest']);
Route::middleware('auth:sanctum')->group(function () {
    // Para padres
    Route::get('/parent/students', [AttendanceController::class, 'myStudents']);
    Route::get('/parent/attendance/{studentId}', [AttendanceController::class, 'studentAttendance']);
});