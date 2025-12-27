<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function daily(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        // Si no se especifica fecha, usar la última con datos
        if (!$request->filled('date')) {
            $latestDate = DB::table('attendances')
                ->select('class_date')
                ->orderBy('class_date', 'desc')
                ->value('class_date');

            $date = $latestDate ?? now()->toDateString();
        } else {
            $date = $request->date;
        }

        $query = DB::table('attendances as a')
            ->join('students as s', 'a.student_id', '=', 's.id')
            ->join('grades as g', 's.grade_id', '=', 'g.id')
            ->where('a.class_date', $date);
        // Filtro por escuela
        if ($user->hasRole('school_admin')) {
            $schoolIds = $user->schools()->pluck('schools.id');
            $query->whereIn('s.school_id', $schoolIds);
        }

        if ($request->filled('school_id')) {
            $query->where('s.school_id', $request->school_id);
        }

        if ($request->filled('grade_id')) {
            $query->where('s.grade_id', $request->grade_id);
        }

        $report = $query->select(
            's.id as student_id',
            's.first_name',
            's.last_name',
            's.student_code',
            'g.name as grade',
            'a.status',
            'a.check_in_at',
            'a.check_out_at',
            'a.method'
        )
            ->orderBy('g.name')
            ->orderBy('s.last_name')
            ->get();

        // Resumen por estado
        $summary = $report->groupBy('status')->map->count();

        return response()->json([
            'success' => true,
            'date' => $date,
            'summary' => $summary,
            'total_students' => $report->count(),
            'data' => $report
        ]);
    }
    // En el mismo ReportController, añade:
    public function range(Request $request)
    {
        $user = $request->user();

        if (!$user->hasAnyRole(['admin', 'school_admin'])) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'school_id' => 'nullable|exists:schools,id',
            'grade_id' => 'nullable|exists:grades,id',
        ]);

        $query = DB::table('attendances as a')
            ->join('students as s', 'a.student_id', '=', 's.id')
            ->join('grades as g', 's.grade_id', '=', 'g.id')
            ->whereBetween('a.class_date', [$validated['start_date'], $validated['end_date']]);

        // Filtros
        if ($user->hasRole('school_admin')) {
            $schoolIds = $user->schools()->pluck('schools.id');
            $query->whereIn('s.school_id', $schoolIds);
        }

        if ($request->filled('school_id')) {
            $query->where('s.school_id', $request->school_id);
        }

        if ($request->filled('grade_id')) {
            $query->where('s.grade_id', $request->grade_id);
        }

        // Datos agrupados por día y estado
        $report = $query->select(
            'a.class_date',
            'a.status',
            DB::raw('COUNT(*) as count')
        )
            ->groupBy('a.class_date', 'a.status')
            ->orderBy('a.class_date', 'desc')
            ->get()
            ->groupBy('class_date');

        return response()->json([
            'success' => true,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'data' => $report
        ]);
    }
    public function lateReport(Request $request)
    {
        $user = $request->user();

        if (!$user->hasAnyRole(['admin', 'school_admin'])) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'month' => 'nullable|date_format:Y-m',
            'student_id' => 'nullable|exists:students,id',
        ]);

        $month = $validated['month'] ?? Carbon::now()->format('Y-m');

        $query = DB::table('attendances as a')
            ->join('students as s', 'a.student_id', '=', 's.id')
            ->join('grades as g', 's.grade_id', '=', 'g.id')
            ->where('a.status', 'late')
            ->whereYear('a.class_date', substr($month, 0, 4))
            ->whereMonth('a.class_date', substr($month, 5, 2));

        if ($user->hasRole('school_admin')) {
            $schoolIds = $user->schools()->pluck('schools.id');
            $query->whereIn('s.school_id', $schoolIds);
        }

        if ($request->filled('student_id')) {
            $query->where('s.id', $request->student_id);
        }

        $lates = $query->select(
            's.id as student_id',
            's.first_name',
            's.last_name',
            's.student_code',
            'g.name as grade',
            'a.class_date',
            'a.check_in_at',
            DB::raw('COUNT(*) OVER(PARTITION BY s.id) as total_lates')
        )
            ->orderBy('total_lates', 'desc')
            ->orderBy('a.class_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'month' => $month,
            'total_lates' => $lates->count(),
            'data' => $lates
        ]);
    }
    public function absenceReport(Request $request)
    {
        $user = $request->user();

        if (!$user->hasAnyRole(['admin', 'school_admin'])) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'month' => 'nullable|date_format:Y-m',
        ]);

        $month = $validated['month'] ?? Carbon::now()->format('Y-m');
        $year = substr($month, 0, 4);
        $monthNum = substr($month, 5, 2);

        // Estudiantes con más ausencias
        $query = DB::table('attendances as a')
            ->join('students as s', 'a.student_id', '=', 's.id')
            ->join('grades as g', 's.grade_id', '=', 'g.id')
            ->where('a.status', 'absent')
            ->whereYear('a.class_date', $year)
            ->whereMonth('a.class_date', $monthNum);

        if ($user->hasRole('school_admin')) {
            $schoolIds = $user->schools()->pluck('schools.id');
            $query->whereIn('s.school_id', $schoolIds);
        }

        $absences = $query->select(
            's.id as student_id',
            's.first_name',
            's.last_name',
            's.student_code',
            'g.name as grade',
            DB::raw('COUNT(*) as total_absences'),
            DB::raw('GROUP_CONCAT(DISTINCT a.class_date ORDER BY a.class_date) as absence_dates')
        )
            ->groupBy('s.id', 's.first_name', 's.last_name', 's.student_code', 'g.name')
            ->orderBy('total_absences', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'month' => $month,
            'total_absences' => $absences->sum('total_absences'),
            'students_with_absences' => $absences->count(),
            'data' => $absences
        ]);
    }
}
