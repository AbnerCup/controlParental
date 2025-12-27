<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Role;
use App\Models\School;
use App\Models\Grade;
use App\Models\SchoolCalendarDay;
use App\Models\GradeSchedule;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\Device;
use App\Models\DeviceAssignment;
use App\Models\ApiClient;

class DemoSchoolSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $tz = 'America/La_Paz';
            $nowUtc = Carbon::now('UTC');
            $todayLocal = Carbon::now($tz)->toDateString();

            $school = School::firstOrCreate(
                ['code' => 'UEP-001'],
                [
                    'name' => 'Unidad Educativa Pioneros',
                    'timezone' => $tz,
                    'city' => 'Oruro',
                    'address' => 'Av. Demo #123',
                    'status' => 'active',
                ]
            );

            $roleIds = Role::pluck('id', 'key');

            $admin = User::firstOrCreate(
                ['email' => 'admin@demo.local'],
                ['name' => 'Admin Demo', 'password' => Hash::make('Password123!'), 'status' => 'active']
            );
            $schoolAdmin = User::firstOrCreate(
                ['email' => 'soledad.admin@demo.local'],
                ['name' => 'Soledad Admin', 'password' => Hash::make('Password123!'), 'status' => 'active']
            );
            $operator = User::firstOrCreate(
                ['email' => 'oscar.operator@demo.local'],
                ['name' => 'Oscar Operator', 'password' => Hash::make('Password123!'), 'status' => 'active']
            );
            $guardianUser = User::firstOrCreate(
                ['email' => 'gabriela.guardian@demo.local'],
                ['name' => 'Gabriela Guardian', 'password' => Hash::make('Password123!'), 'status' => 'active']
            );

            $admin->roles()->syncWithoutDetaching([$roleIds['admin'] ?? null]);
            $schoolAdmin->roles()->syncWithoutDetaching([$roleIds['school_admin'] ?? null]);
            $operator->roles()->syncWithoutDetaching([$roleIds['operator'] ?? null]);
            $guardianUser->roles()->syncWithoutDetaching([$roleIds['guardian'] ?? null]);

            foreach ([$schoolAdmin, $operator, $guardianUser] as $u) {
                DB::table('user_schools')->updateOrInsert(
                    ['user_id' => $u->id, 'school_id' => $school->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }

            $grade = Grade::firstOrCreate(
                ['school_id' => $school->id, 'name' => 'Inicial A'],
                ['status' => 'active']
            );

            SchoolCalendarDay::updateOrCreate(
                ['school_id' => $school->id, 'day' => $todayLocal],
                ['day_type' => 'class', 'note' => 'Jornada regular']
            );

            foreach ([1, 2, 3, 4, 5] as $weekday) {
                GradeSchedule::updateOrCreate(
                    ['grade_id' => $grade->id, 'weekday' => $weekday, 'effective_from' => $todayLocal],
                    ['start_time' => '08:30:00', 'end_time' => '12:00:00', 'late_grace_minutes' => 10, 'effective_to' => null]
                );
            }

            $guardian = Guardian::firstOrCreate(
                ['email' => 'gabriela.guardian@demo.local'],
                [
                    'user_id' => $guardianUser->id,
                    'first_name' => 'Gabriela',
                    'last_name' => 'Copa',
                    'phone' => '+59170000001',
                    'status' => 'active',
                ]
            );

            $student = Student::firstOrCreate(
                ['student_code' => 'STU-0001'],
                [
                    'school_id' => $school->id,
                    'grade_id' => $grade->id,
                    'user_id' => null,
                    'first_name' => 'Mateo',
                    'last_name' => 'Copa',
                    'birth_date' => '2019-05-03',
                    'status' => 'active',
                ]
            );

            StudentGuardian::updateOrCreate(
                ['student_id' => $student->id, 'guardian_id' => $guardian->id, 'start_date' => $todayLocal],
                ['relationship' => 'father', 'is_primary' => true, 'end_date' => null]
            );

            $device = Device::firstOrCreate(
                ['uid' => 'RFID-DEMO-0001'],
                ['school_id' => $school->id, 'type' => 'rfid', 'status' => 'active']
            );

            DeviceAssignment::updateOrCreate(
                ['device_id' => $device->id, 'student_id' => $student->id, 'assigned_at' => $nowUtc],
                ['unassigned_at' => null, 'note' => 'Asignación demo']
            );

            $keyId = 'demo-key-001';
            $secret = Str::random(32);
            $secretHash = hash('sha256', $secret);
            $secretEnc = Crypt::encryptString($secret);

            $apiClient = ApiClient::where('key_id', $keyId)->first();
            if (!$apiClient) {
                $apiClient = ApiClient::create([
                    'school_id' => $school->id,
                    'name' => 'Demo Reader 1',
                    'type' => 'gateway',
                    'key_id' => $keyId,
                    'key_secret_hash' => $secretHash,
                    'key_secret_enc' => $secretEnc,
                    'active' => true,
                ]);
            } else {
                $apiClient->update([
                    'key_secret_hash' => $secretHash,
                    'key_secret_enc' => $secretEnc,
                    'active' => true,
                ]);
            }

            $this->command->info('--- DEMO SEED COMPLETO ---');
            $this->command->info("Escuela: {$school->name} ({$school->timezone})");
            $this->command->info("Grado: {$grade->name}");
            $this->command->info("Estudiante: {$student->first_name} {$student->last_name} [code={$student->student_code}]");
            $this->command->info("Device UID: {$device->uid}");
            $this->command->info("API Client key_id: {$apiClient->key_id}");
            $this->command->warn("API Client SECRET (usa este valor para firmar HMAC): {$secret}");
            $this->command->info("Hoy lectivo (local): {$todayLocal}");
            $this->command->info('---------------------------');
        });
    }
}
