<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use DB;
use Illuminate\Auth\Access\Response;

class StudentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Student $student): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Student $student): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Student $student): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Student $student): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Student $student): bool
    {
        return false;
    }
    public function viewAttendance(User $user, Student $student): bool
    {
        // 1. ADMIN global - puede ver todo
        if ($user->hasRole('admin')) {
            return true;
        }

        // 2. ADMIN ESCUELA u OPERADOR - solo estudiantes de su escuela
        if ($user->hasAnyRole(['school_admin', 'operator'])) {
            // Verificar que el usuario tenga acceso a la escuela del estudiante
            return $user->schools()->where('schools.id', $student->school_id)->exists();
        }

        // 3. PADRE/TUTOR - solo sus hijos
        if ($user->hasRole('guardian') && $user->guardian) {
            // Buscar en la tabla student_guardians si es tutor de este estudiante
            return DB::table('student_guardians')
                ->where('student_id', $student->id)
                ->where('guardian_id', $user->guardian->id)
                ->whereNull('end_date') // relación activa
                ->exists();
        }

        // 4. Por defecto: NO permitir
        return false;
    }
}
