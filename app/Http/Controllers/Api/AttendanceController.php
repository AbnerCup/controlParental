<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', DailyAttendance::class);

        $user = Auth::user();
        $query = DailyAttendance::query();

        if ($user->role === 'school_admin') {
            $query->whereIn('student_id', function ($query) use ($user) {
                $query->select('id')->from('students')->where('school_id', $user->school_id);
            });
        } elseif ($user->role === 'guardian') {
            $query->whereIn('student_id', $user->guardianProfile->students()->pluck('id'));
        }

        // Add filtering by date range if needed
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('class_date', [$request->start_date, $request->end_date]);
        }

        return $query->with('student')->get();
    }
}
