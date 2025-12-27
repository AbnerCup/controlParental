<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\School;
use DB;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->hasRole('admin')) {
            return response()->json(['error' => 'Solo administradores globales'], 403);
        }

        $schools = School::where('status', 'active')
            ->select('id', 'name', 'code', 'timezone', 'city', 'created_at')
            ->get();

        return response()->json([
            'success' => true,
            'schools' => $schools
        ]);
    }

    public function students($schoolId, Request $request)
    {
        $user = $request->user();

        // Admin global o school_admin de ESA escuela
        if (
            $user->hasRole('admin') ||
            ($user->hasRole('school_admin') && $user->schools()->where('schools.id', $schoolId)->exists())
        ) {

            $students = DB::table('students as s')
                ->join('grades as g', 's.grade_id', '=', 'g.id')
                ->where('s.school_id', $schoolId)
                ->where('s.status', 'active')
                ->select('s.id', 's.first_name', 's.last_name', 's.student_code', 'g.name as grade')
                ->get();

            return response()->json([
                'success' => true,
                'school_id' => $schoolId,
                'students' => $students
            ]);
        }

        return response()->json(['error' => 'No autorizado'], 403);
    }
}
