<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendAttendanceNotification;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Services\HmacClientValidator;
use App\Models\Device;
use App\Models\DeviceAssignment;
use App\Models\School;
use App\Models\AttendanceEvent;
use App\Models\Attendance;

class DeviceGatewayController extends Controller
{
    public function ingest(Request $req, HmacClientValidator $validator)
    {
        $keyId = (string) $req->header('X-Api-Key-Id');
        $signature = (string) $req->header('X-Signature');
        $rawBody = $req->getContent();

        $apiClient = $validator->validate($keyId, $signature, $rawBody);
        if (!$apiClient) {
            return response()->json(['error' => 'invalid_signature'], 401);
        }

        $data = $req->validate([
            'uid' => 'required|string',
            'event_type' => 'required|in:check_in,check_out,panic',
            'occurred_at' => 'required|date',
            'meta' => 'array'
        ]);
        $device = Device::where('uid', $data['uid'])->where('status', 'active')->first();
        if (!$device) {
            return response()->json(['error' => 'device_not_found_or_inactive'], 404);
        }

        $assignment = DeviceAssignment::where('device_id', $device->id)
            ->whereNull('unassigned_at')->latest('assigned_at')->first();

        if (!$assignment) {
            return response()->json(['error' => 'no_active_assignment'], 422);
        }

        $studentId = $assignment->student_id;
        $schoolId = $device->school_id;
        $occurredAtUtc = Carbon::parse($data['occurred_at'])->utc();

        if ($data['event_type'] === 'panic') {
            AttendanceEvent::create([
                'school_id' => $schoolId,
                'student_id' => $studentId,
                'device_id' => $device->id,
                'event_type' => 'correction',
                'source' => 'device',
                'occurred_at' => $occurredAtUtc,
                'actor_user_id' => null,
                'payload' => $data['meta'] ?? null,
            ]);
            return response()->json(['ok' => true, 'note' => 'panic_event_received']);
        }
        AttendanceEvent::create([
            'school_id' => $schoolId,
            'student_id' => $studentId,
            'device_id' => $device->id,
            'event_type' => $data['event_type'],
            'source' => 'device',
            'occurred_at' => $occurredAtUtc,
            'actor_user_id' => null,
            'payload' => $data['meta'] ?? null,
        ]);

        $school = School::findOrFail($schoolId);
        $local = Carbon::parse($occurredAtUtc)->tz($school->timezone);
        $classDate = $local->toDateString();
        $isClassDay = $school->calendarDays()
            ->where('day', $classDate)
            ->where('day_type', 'class')
            ->exists();
        if (!$isClassDay) {
            return response()->json(['ok' => true, 'note' => 'non_class_day_event_recorded']);
        }


        $att = Attendance::firstOrNew([
            'student_id' => $studentId,
            'class_date' => $classDate,
        ], ['school_id' => $schoolId]);

        if ($data['event_type'] === 'check_in' && !$att->check_in_at) {
            $att->check_in_at = $occurredAtUtc;
            $att->device_id = $device->id;

            $att->method = 'device_scan';

            $weekday = $local->dayOfWeek;
            $schedule = $school->gradeSchedules()
                ->where('grade_id', $assignment->student->grade_id)
                ->where('weekday', $weekday)
                ->where('effective_from', '<=', $classDate)
                ->where(function ($q) use ($classDate) {
                    $q->whereNull('effective_to')->orWhere('effective_to', '>=', $classDate);
                })
                ->first();

            if ($schedule) {
                $startTimeRaw = $schedule->start_time;
                $startTimeStr = strlen($startTimeRaw) === 5 ? $startTimeRaw . ':00' : $startTimeRaw;

                $startLocal = Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    "{$classDate} {$startTimeStr}",
                    $school->timezone
                );
                $deadline = $startLocal->copy()->addMinutes($schedule->late_grace_minutes ?? 0);

                $att->status = $local->gt($deadline) ? 'late' : 'present';
            } else {
                $att->status = 'present';
            }
        }

        if ($data['event_type'] === 'check_out' && !$att->check_out_at) {
            $att->check_out_at = $occurredAtUtc;
        }

        $att->save();
        dispatch(new SendAttendanceNotification($att->id));
        return response()->json(['ok' => true, 'attendance_id' => $att->id]);

    }
}
