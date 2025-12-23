<?php

namespace Tests\Feature;

use App\Models\DailyAttendance;
use App\Models\GuardianProfile;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttendanceApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /** @test */
    public function unauthenticated_users_cannot_access_attendances_endpoint()
    {
        $response = $this->getJson('/api/attendances');
        $response->assertStatus(401);
    }

    /** @test */
    public function school_admin_can_view_attendances_for_their_school_only()
    {
        $school1 = School::factory()->create();
        $school2 = School::factory()->create();

        $admin1 = User::factory()->create(['school_id' => $school1->id, 'role' => 'school_admin']);
        $student1 = Student::factory()->create(['school_id' => $school1->id]);
        $attendance1 = DailyAttendance::factory()->create(['student_id' => $student1->id]);

        $student2 = Student::factory()->create(['school_id' => $school2->id]);
        $attendance2 = DailyAttendance::factory()->create(['student_id' => $student2->id]);

        Sanctum::actingAs($admin1);

        $response = $this->getJson('/api/attendances');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['id' => $attendance1->id]);
        $response->assertJsonMissing(['id' => $attendance2->id]);
    }

    /** @test */
    public function guardian_can_view_attendances_for_their_children_only()
    {
        $school = School::factory()->create();

        $guardianUser = User::factory()->create(['school_id' => $school->id, 'role' => 'guardian']);
        $guardianProfile = GuardianProfile::factory()->create(['user_id' => $guardianUser->id]);

        $student1 = Student::factory()->create(['school_id' => $school->id]);
        $student2 = Student::factory()->create(['school_id' => $school->id]);

        $guardianProfile->students()->attach($student1);

        $attendance1 = DailyAttendance::factory()->create(['student_id' => $student1->id]);
        $attendance2 = DailyAttendance::factory()->create(['student_id' => $student2->id]);

        Sanctum::actingAs($guardianUser);

        $response = $this->getJson('/api/attendances');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['id' => $attendance1->id]);
        $response->assertJsonMissing(['id' => $attendance2->id]);
    }
}
