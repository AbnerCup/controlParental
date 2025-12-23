<?php

namespace App\Jobs;

use App\Models\School;
use App\Models\Student;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class NightlyAbsenceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The school instance.
     *
     * @var \App\Models\School
     */
    protected $school;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(School $school)
    {
        $this->school = $school;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $classDate = Carbon::now($this->school->timezone)->toDateString();

        $absentStudents = Student::where('school_id', $this->school->id)
            ->whereDoesntHave('dailyAttendances', function ($query) use ($classDate) {
                $query->where('class_date', $classDate);
            })
            ->with('guardians.user')
            ->get();

        foreach ($absentStudents as $student) {
            foreach ($student->guardians as $guardian) {
                Notification::create([
                    'user_id' => $guardian->user->id,
                    'type' => 'student.absent',
                    'channel' => 'push', // Default channel, can be configured later
                    'content' => "Estimado tutor, su hijo(a) {$student->first_name} {$student->last_name} estuvo ausente hoy, {$classDate}.",
                ]);
            }
        }
    }
}
