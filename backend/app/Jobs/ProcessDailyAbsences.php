<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\School;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\SchoolCalendarDay;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessDailyAbsences implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Ejecutar el job
     */
    public function handle(): void
    {
        Log::info('Iniciando proceso de ausencias diarias');

        // Procesar cada escuela activa
        School::where('status', 'active')->chunk(100, function ($schools) {
            foreach ($schools as $school) {
                $this->processSchool($school);
            }
        });

        Log::info('Proceso de ausencias diarias completado');
    }

    /**
     * Procesar una escuela específica
     */
    private function processSchool(School $school): void
    {
        // Obtener fecha local de la escuela (ayer, porque se ejecuta de noche)
        $dateLocal = Carbon::now($school->timezone)->subDay()->toDateString();

        Log::info("Procesando escuela {$school->name} para fecha {$dateLocal}");

        // Verificar si es día de clase
        $isClassDay = SchoolCalendarDay::where('school_id', $school->id)
            ->where('day', $dateLocal)
            ->where('day_type', 'class')
            ->exists();

        if (!$isClassDay) {
            Log::info("{$dateLocal} no es día de clase en {$school->name}");
            return;
        }

        // Obtener todos los estudiantes activos de la escuela
        Student::where('school_id', $school->id)
            ->where('status', 'active')
            ->chunk(200, function ($students) use ($school, $dateLocal) {
                foreach ($students as $student) {
                    $this->processStudent($student, $dateLocal);
                }
            });
    }

    /**
     * Procesar un estudiante específico
     */
    private function processStudent(Student $student, string $dateLocal): void
    {
        // Verificar si ya tiene registro de asistencia para esa fecha
        $existingAttendance = Attendance::where('student_id', $student->id)
            ->where('class_date', $dateLocal)
            ->first();

        // Si no existe registro O existe pero no tiene check_in
        if (!$existingAttendance || !$existingAttendance->check_in_at) {
            // Crear o actualizar registro como ausente
            Attendance::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'class_date' => $dateLocal,
                ],
                [
                    'school_id' => $student->school_id,
                    'status' => 'absent',
                    'method' => 'import', // Marcado por sistema
                    'check_in_at' => null,
                    'check_out_at' => null,
                ]
            );

            Log::info("Estudiante {$student->id} marcado como ausente para {$dateLocal}");
        }
    }
}