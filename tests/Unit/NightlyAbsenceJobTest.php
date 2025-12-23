<?php

namespace Tests\Unit;

use App\Jobs\NightlyAbsenceJob;
use App\Models\DailyAttendance;
use App\Models\GuardianProfile;
use App\Models\Notification;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NightlyAbsenceJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /** @test */
    public function it_creates_notifications_for_absent_students()
    {
        Queue::fake();

        $school = School::factory()->create(['timezone' => 'America/La_Paz']);
        $today = Carbon::now($school->timezone)->toDateString();

        // --- Present Student ---
        $presentStudent = Student::factory()->create(['school_id' => $school->id]);
        DailyAttendance::factory()->create([
            'student_id' => $presentStudent->id,
            'class_date' => $today,
            'status' => 'present',
        ]);
        $guardianUser1 = User::factory()->create(['school_id' => $school->id, 'role' => 'guardian']);
        $guardianProfile1 = GuardianProfile::factory()->create(['user_id' => $guardianUser1->id]);
        $guardianProfile1->students()->attach($presentStudent);

        // --- Absent Student ---
        $absentStudent = Student::factory()->create(['school_id' => $school->id]);
        $guardianUser2 = User::factory()->create(['school_id' => $school->id, 'role' => 'guardian']);
        $guardianProfile2 = GuardianProfile::factory()->create(['user_id' => $guardianUser2->id]);
        $guardianProfile2->students()->attach($absentStudent);

        $job = new NightlyAbsenceJob($school);
        $job->handle();

        $this->assertDatabaseCount('notifications', 1);
        $notification = Notification::first();
        $this->assertEquals($guardianUser2->id, $notification->user_id);
        $this->assertEquals('student.absent', $notification->type);
        $this->assertStringContainsString($absentStudent->first_name, $notification->content);
    }
}
