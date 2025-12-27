<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $user = $request->user();

        // Solo admin y school_admin
        if (!$user->hasAnyRole(['admin', 'school_admin'])) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $today = Carbon::today()->toDateString();

        // Si es school_admin, filtrar por su escuela
        $schoolIds = $user->schools()->pluck('schools.id');

        $stats = [
            'total_students' => DB::table('students')
                ->when($user->hasRole('school_admin'), function ($query) use ($schoolIds) {
                    return $query->whereIn('school_id', $schoolIds);
                })
                ->where('status', 'active')
                ->count(),

            'today_attendance' => [
                'present' => DB::table('attendances')
                    ->when($user->hasRole('school_admin'), function ($query) use ($schoolIds) {
                        return $query->whereIn('school_id', $schoolIds);
                    })
                    ->where('class_date', $today)
                    ->where('status', 'present')
                    ->count(),

                'late' => DB::table('attendances')
                    ->when($user->hasRole('school_admin'), function ($query) use ($schoolIds) {
                        return $query->whereIn('school_id', $schoolIds);
                    })
                    ->where('class_date', $today)
                    ->where('status', 'late')
                    ->count(),

                'absent' => DB::table('attendances')
                    ->when($user->hasRole('school_admin'), function ($query) use ($schoolIds) {
                        return $query->whereIn('school_id', $schoolIds);
                    })
                    ->where('class_date', $today)
                    ->where('status', 'absent')
                    ->count(),
            ],

            'total_schools' => $user->hasRole('admin')
                ? DB::table('schools')->where('status', 'active')->count()
                : $schoolIds->count(),

            'active_devices' => DB::table('devices')
                ->when($user->hasRole('school_admin'), function ($query) use ($schoolIds) {
                    return $query->whereIn('school_id', $schoolIds);
                })
                ->where('status', 'active')
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'date' => $today,
            'stats' => $stats
        ]);
    }
}
