<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PanicController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SchoolController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceGatewayController;
use Illuminate\Http\Request;

Route::post('/gateway/device-events', [DeviceGatewayController::class, 'ingest']);

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    try {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Contraseña incorrecta'
            ], 401);
        }

        $roles = $user->roles()->pluck('key')->toArray();
        $schools = $user->schools()->pluck('schools.id', 'schools.name')->toArray();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $roles,
                'schools' => $schools,
                'primary_role' => count($roles) > 0 ? $roles[0] : 'user',
                'primary_school_id' => count($schools) > 0 ? array_values($schools)[0] : null
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Error en login: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Error interno del servidor'
        ], 500);
    }
});


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
    // routes/api.php - dentro de auth:sanctum
    Route::prefix('panic')->group(function () {
        Route::post('/trigger', [PanicController::class, 'trigger']);
        Route::get('/events', [PanicController::class, 'list']);
        Route::post('/events/{id}/resolve', [PanicController::class, 'resolve']);
    });
});

