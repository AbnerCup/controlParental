<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Attendance;
use App\Models\StudentGuardian;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendAttendanceNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $attendanceId;

    public function __construct(int $attendanceId)
    {
        $this->attendanceId = $attendanceId;
    }

    public function handle(): void
    {
        $attendance = Attendance::with(['student.guardians', 'student.school'])->find($this->attendanceId);
        if (!$attendance)
            return;
        $student = $attendance->student;
        $school = $attendance->school;
        $relations = StudentGuardian::where('student_id', $student->id)
            ->where('is_primary', true)
            ->whereNull('end_date')
            ->with('guardian')
            ->get();

        if ($relations->isEmpty()) {
            Log::info('SendAttendanceNotification: no guardians found', ['attendance_id' => $attendance->id]);
            return;
        }
        $isLate = ($attendance->status === 'late');
        $templateKey = $isLate ? 'attendance.late' : 'attendance.check_in';

        foreach ($relations as $rel) {
            $g = $rel->guardian;
            if (!$g)
                continue;
            if (!$g->email) {
                Log::warning('Guardian without email, skipping notification', [
                    'guardian_id' => $g->id,
                    'attendance_id' => $attendance->id,
                ]);
                continue;
            }
            $payload = [
                'student' => [
                    'id' => $student->id,
                    'name' => "{$student->first_name} {$student->last_name}",
                ],
                'attendance' => [
                    'id' => $attendance->id,
                    'class_date' => $attendance->class_date,
                    'status' => $attendance->status,
                    'check_in_at' => optional($attendance->check_in_at)->toIso8601String(),
                    'check_out_at' => optional($attendance->check_out_at)->toIso8601String(),
                ],
                'school' => [
                    'id' => $school->id,
                    'name' => $school->name,
                    'timezone' => $school->timezone,
                ],
            ];

            $notif = Notification::create([
                'school_id' => $school->id,
                'guardian_id' => $g->id,
                'channel' => 'email',
                'template_key' => $templateKey,
                'payload' => $payload,
                'status' => 'queued',
                'error_message' => null,
                'related_type' => 'attendance',
                'related_id' => $attendance->id,
                'queued_at' => Carbon::now('UTC'),
            ]);

            try {
                $subject = "[{$school->name}] Asistencia de {$student->first_name}";
                $statusLabel = strtoupper($attendance->status ?? 'PRESENT');
                $message = "Estimado/a {$g->first_name},\n\n"
                    . "Su hijo/a {$student->first_name} {$student->last_name} registró asistencia.\n"
                    . "Estado: {$statusLabel}\n"
                    . "Fecha (local): {$attendance->class_date}\n"
                    . "Hora ingreso (UTC): " . ($attendance->check_in_at ? $attendance->check_in_at->format('Y-m-d H:i:s') : 'N/A') . "\n\n"
                    . "Gracias por usar el sistema de control parental.";

                Mail::raw($message, function ($mail) use ($g, $subject) {
                    $mail->to($g->email)->subject($subject);
                });

                $notif->update([
                    'status' => 'sent',
                    'sent_at' => Carbon::now('UTC'),
                ]);
            } catch (\Throwable $e) {
                Log::error('Attendance notification failed', [
                    'notification_id' => $notif->id,
                    'guardian_id' => $g->id,
                    'error' => $e->getMessage(),
                ]);

                $notif->update([
                    'status' => 'failed',
                    'error_message' => mb_strimwidth($e->getMessage(), 0, 240),
                ]);
            }
        }
    }
}
