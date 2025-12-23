<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * GET /api/parent/students
     * Lista los estudiantes del padre autenticado
     */
    public function myStudents(Request $request)
    {
        $user = $request->user();

        // Verificar que sea tutor
        if (!$user->guardian) {
            return response()->json(['error' => 'No tienes permisos de tutor'], 403);
        }

        // Obtener estudiantes usando DB::table (directo y claro)
        $students = DB::table('student_guardians')
            ->join('students', 'student_guardians.student_id', '=', 'students.id')
            ->join('grades', 'students.grade_id', '=', 'grades.id')
            ->where('student_guardians.guardian_id', $user->guardian->id)
            ->whereNull('student_guardians.end_date')
            ->select(
                'students.id',
                'students.first_name',
                'students.last_name',
                'students.student_code',
                'students.birth_date',
                'grades.name as grade_name'
            )
            ->get();

        return response()->json([
            'success' => true,
            'count' => $students->count(),
            'students' => $students
        ]);
    }

    /**
     * GET /api/parent/attendance/{studentId}
     * Obtiene la asistencia de un estudiante específico
     */
    // En el método studentAttendance del controlador
    public function studentAttendance(Request $request, $studentId)
    {
        $user = $request->user();

        // Verificar permisos
        $isParent = DB::table('student_guardians')
            ->where('student_id', $studentId)
            ->where('guardian_id', optional($user->guardian)->id)
            ->whereNull('end_date')
            ->exists();

        if (!$isParent) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Solo los campos necesarios
        $query = DB::table('attendances')
            ->where('student_id', $studentId)
            ->orderBy('class_date', 'desc')
            ->select([
                'id',
                'class_date',
                'status',
                'check_in_at',
                'check_out_at',
                'method'
            ]);

        // Aplicar filtros
        if ($request->filled('month')) {
            $query->whereMonth('class_date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('class_date', $request->year);
        }

        // Obtener datos SIN paginación compleja
        $attendances = $query->get();

        // Formatear fechas
        $formatted = $attendances->map(function ($item) {
            return [
                'id' => $item->id,
                'fecha' => $item->class_date,
                'estado' => $item->status,
                'entrada' => $item->check_in_at ? substr($item->check_in_at, 11, 5) : null,
                'salida' => $item->check_out_at ? substr($item->check_out_at, 11, 5) : null,
                'metodo' => $item->method
            ];
        });

        return response()->json([
            'success' => true,
            'student_id' => $studentId,
            'count' => $formatted->count(),
            'attendance' => $formatted
        ]);
    }
}