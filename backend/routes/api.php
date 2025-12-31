<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\PanicController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SchoolController;
use App\Http\Controllers\Api\StudentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceGatewayController;

// --- RUTAS PÚBLICAS O DE DISPOSITIVOS ---
Route::post('/gateway/device-events', [DeviceGatewayController::class, 'ingest']);
Route::post('/login', [LoginController::class, 'login']);

// --- RUTAS PROTEGIDAS ---
Route::middleware('auth:sanctum')->group(function () {

    // --- MÓDULO: GESTIÓN GLOBAL (Normalmente Super Admin) ---
    Route::prefix('admin')->group(function () {

        // Escuelas
        Route::middleware('permission:schools.manage')->group(function () {
            Route::get('/schools', [SchoolController::class, 'index']);
            Route::get('/schools/{id}', [SchoolController::class, 'show']);
            Route::post('/schools', [SchoolController::class, 'store']);
            Route::put('/schools/{id}', [SchoolController::class, 'update']);
            Route::delete('/schools/{id}', [SchoolController::class, 'destroy']);
        });

        // Usuarios Administrativos
        Route::middleware('permission:users.manage')->group(function () {
            Route::post('/users', [UserController::class, 'store']);
            Route::post('/assign-school-admin', [UserController::class, 'assignToSchool']);
        });

        // --- MÓDULO: GESTIÓN ESCOLAR (Admin de Escuela / Operador) ---

        // Alumnos
        Route::middleware('permission:students.manage')->group(function () {
            Route::get('/students', [StudentController::class, 'index']);
            Route::get('/schools/{schoolId}/students', [SchoolController::class, 'students']);
        });

        // Asistencias y Reportes
        Route::get('/dashboard', [DashboardController::class, 'stats']); // Dashboard general

        Route::middleware('permission:attendance.view')->group(function () {
            Route::get('/attendance', [AttendanceController::class, 'index']);
            Route::post('/attendance/manual', [AttendanceController::class, 'storeManual']);

            // Sub-prefijo para Reportes
            Route::prefix('reports')->group(function () {
                Route::get('/daily', [ReportController::class, 'daily']);
                Route::get('/range', [ReportController::class, 'range']);
                Route::get('/lates', [ReportController::class, 'lateReport']);
                Route::get('/absences', [ReportController::class, 'absenceReport']);
            });
        });

        // Pánico
        Route::middleware('permission:panics.manage')->prefix('panic')->group(function () {
            Route::get('/events', [PanicController::class, 'list']);
            Route::post('/events/{id}/resolve', [PanicController::class, 'resolve']);
            Route::post('/events/{id}/acknowledge', [PanicController::class, 'acknowledge']);
        });
    });

    // --- MÓDULO: PADRES / TUTORES (No requieren "manage", solo sus datos) ---
    Route::prefix('parent')->group(function () {
        Route::get('/students', [AttendanceController::class, 'myStudents']);
        Route::get('/attendance/{studentId}', [AttendanceController::class, 'studentAttendance']);
        Route::post('/panic/trigger', [PanicController::class, 'trigger']); // Los padres pueden disparar pánico
    });

});
