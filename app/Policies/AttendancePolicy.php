<?php

namespace App\Policies;

use App\Models\DailyAttendance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendancePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->role === 'school_admin' || $user->role === 'guardian';
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DailyAttendance  $dailyAttendance
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, DailyAttendance $dailyAttendance)
    {
        if ($user->role === 'school_admin') {
            return $user->school_id === $dailyAttendance->student->school_id;
        }

        if ($user->role === 'guardian') {
            return $user->guardianProfile->students->contains($dailyAttendance->student_id);
        }

        return false;
    }
}
