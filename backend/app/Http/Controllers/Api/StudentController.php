<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use DB;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $schoolIds = $user->schools()->pluck('schools.id');

            if ($schoolIds->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'El usuario no tiene escuelas asignadas'
                ]);
            }

            $students = Student::with([
                'school',
                'guardians' => function ($query) {
                    $query->wherePivot('is_primary', true);
                },
                'activeDeviceAssignment.device'
            ])
                ->whereIn('school_id', $schoolIds)
                ->get()
                ->map(function ($student) {
                    $primaryGuardian = $student->guardians->first();

                    return [
                        'id' => $student->id,
                        'name' => "{$student->first_name} {$student->last_name}",
                        'student_code' => $student->student_code,
                        'grade' => $student->grade ?? 'N/A',
                        'school_name' => $student->school->name ?? 'No asignada',
                        'guardian_name' => $primaryGuardian
                            ? "{$primaryGuardian->first_name} {$primaryGuardian->last_name}"
                            : 'Sin tutor',
                        'guardian_phone' => $primaryGuardian->phone ?? null,
                        'device_uid' => $student->activeDeviceAssignment->device->uid ?? null,
                        'status' => $student->status
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $students
            ]);

        } catch (\Exception $e) {
            \Log::error("Error en Students Index: " . $e->getMessage());
            return response()->json(['error' => 'Error al obtener estudiantes'], 500);
        }
    }
}
