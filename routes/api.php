<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SchoolController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceGatewayController;

Route::post('/gateway/device-events', [DeviceGatewayController::class, 'ingest']);
Route::middleware('auth:sanctum')->group(function () {
    // Para padres
    Route::get('/parent/students', [AttendanceController::class, 'myStudents']);
    Route::get('/parent/attendance/{studentId}', [AttendanceController::class, 'studentAttendance']);


    Route::get('/admin/dashboard', [DashboardController::class, 'stats']);

    // Escuelas
    Route::get('/admin/schools', [SchoolController::class, 'index']);
    Route::get('/admin/schools/{schoolId}/students', [SchoolController::class, 'students']);

    // Asistencias
    Route::get('/admin/attendance', [AttendanceController::class, 'index']);
    Route::post('/admin/attendance/manual', [AttendanceController::class, 'storeManual']);

    // Reportes
    Route::prefix('admin/reports')->group(function () {
        Route::get('/daily', [ReportController::class, 'daily']);
        Route::get('/range', [ReportController::class, 'range']);
        Route::get('/lates', [ReportController::class, 'lateReport']);
        Route::get('/absences', [ReportController::class, 'absenceReport']);
    });
});
