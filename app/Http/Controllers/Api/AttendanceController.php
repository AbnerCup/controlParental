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
    public function index(Request $request)
    {
        $user = $request->user();

        // Por ahora permitir a todos autenticados
        // TODO: Añadir verificación de roles

        $query = DB::table('attendances as a')
            ->join('students as s', 'a.student_id', '=', 's.id')
            ->join('grades as g', 's.grade_id', '=', 'g.id')
            ->join('schools as sc', 's.school_id', '=', 'sc.id');

        // Filtro por escuela
        if ($request->filled('school_id')) {
            $query->where('sc.id', $request->school_id);
        }

        // Filtro por estudiante
        if ($request->filled('student_id')) {
            $query->where('s.id', $request->student_id);
        }

        // Filtro por fecha desde
        if ($request->filled('date_from')) {
            $query->where('a.class_date', '>=', $request->date_from);
        }

        // Filtro por fecha hasta
        if ($request->filled('date_to')) {
            $query->where('a.class_date', '<=', $request->date_to);
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('a.status', $request->status);
        }

        // Seleccionar campos
        $query->select(
            'a.id',
            'a.class_date',
            'a.status',
            'a.check_in_at',
            'a.check_out_at',
            'a.method',
            's.id as student_id',
            's.first_name',
            's.last_name',
            's.student_code',
            'g.name as grade',
            'sc.name as school'
        )->orderBy('a.class_date', 'desc')->orderBy('s.last_name');

        // Paginación
        $perPage = $request->get('per_page', 50);
        $attendances = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'total' => $attendances->total(),
            'per_page' => $attendances->perPage(),
            'current_page' => $attendances->currentPage(),
            'data' => $attendances->items()
        ]);
    }

    public function storeManual(Request $request)
    {
        $user = $request->user();

        if (!$user->hasAnyRole(['admin', 'school_admin', 'operator'])) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_date' => 'required|date',
            'event_type' => 'required|in:check_in,check_out',
            'occurred_at' => 'required|date',
            'status' => 'in:present,late,absent,left_early',
            'notes' => 'nullable|string'
        ]);

        // Verificar permisos sobre el estudiante
        $student = DB::table('students as s')
            ->where('s.id', $validated['student_id'])
            ->first();

        if (
            $user->hasRole('school_admin') &&
            !$user->schools()->where('schools.id', $student->school_id)->exists()
        ) {
            return response()->json(['error' => 'No autorizado para este estudiante'], 403);
        }

        DB::transaction(function () use ($validated, $user, $student) {
            // 1. Registrar evento crudo
            DB::table('attendance_events')->insert([
                'school_id' => $student->school_id,
                'student_id' => $validated['student_id'],
                'event_type' => $validated['event_type'],
                'source' => 'manual',
                'occurred_at' => $validated['occurred_at'],
                'actor_user_id' => $user->id,
                'payload' => json_encode(['notes' => $validated['notes'] ?? '']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Actualizar/crear asistencia diaria
            $attendance = DB::table('attendances')
                ->where('student_id', $validated['student_id'])
                ->where('class_date', $validated['class_date'])
                ->first();

            if ($attendance) {
                // Actualizar existente
                if ($validated['event_type'] === 'check_in') {
                    DB::table('attendances')
                        ->where('id', $attendance->id)
                        ->update([
                            'check_in_at' => $validated['occurred_at'],
                            'status' => $validated['status'] ?? 'present',
                            'method' => 'manual',
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('attendances')
                        ->where('id', $attendance->id)
                        ->update([
                            'check_out_at' => $validated['occurred_at'],
                            'method' => 'manual',
                            'updated_at' => now(),
                        ]);
                }
            } else {
                // Crear nuevo
                DB::table('attendances')->insert([
                    'school_id' => $student->school_id,
                    'student_id' => $validated['student_id'],
                    'class_date' => $validated['class_date'],
                    'check_in_at' => $validated['event_type'] === 'check_in' ? $validated['occurred_at'] : null,
                    'check_out_at' => $validated['event_type'] === 'check_out' ? $validated['occurred_at'] : null,
                    'status' => $validated['status'] ?? 'present',
                    'method' => 'manual',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Asistencia manual registrada'
        ]);
    }
}