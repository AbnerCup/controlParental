<?php
// database/seeders/TestDataSeeder.php - VERSIÓN FINAL CORREGIDA

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Models\School;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Device;
use App\Models\DeviceAssignment;
use App\Models\ApiClient;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        echo "=== CREANDO DATOS DE PRUEBA ===\n\n";

        // 1. ESCUELA
        $school = School::first() ?: School::create([
            'name' => 'Escuela Test',
            'code' => 'TEST001',
            'timezone' => 'America/La_Paz',
            'city' => 'La Paz',
            'status' => 'active',
        ]);
        echo "✅ Escuela: {$school->name}\n";

        // 2. GRADO
        $grade = Grade::where('school_id', $school->id)->first() ?: Grade::create([
            'school_id' => $school->id,
            'name' => 'Kínder A',
            'status' => 'active',
        ]);
        echo "✅ Grado: {$grade->name}\n";

        // 3. ESTUDIANTE
        $student = Student::where('student_code', 'STU001')->first() ?: Student::create([
            'school_id' => $school->id,
            'grade_id' => $grade->id,
            'first_name' => 'Ana',
            'last_name' => 'Gonzales',
            'student_code' => 'STU001',
            'birth_date' => '2019-05-15',
            'status' => 'active',
        ]);
        echo "✅ Estudiante: {$student->first_name}\n";

        // 4. PADRE
        $guardian = Guardian::where('email', 'padre@ejemplo.com')->first() ?: Guardian::create([
            'first_name' => 'Carlos',
            'last_name' => 'Gonzales',
            'email' => 'padre@ejemplo.com',
            'phone' => '77712345',
        ]);

        if (!DB::table('student_guardians')->where('student_id', $student->id)->exists()) {
            DB::table('student_guardians')->insert([
                'student_id' => $student->id,
                'guardian_id' => $guardian->id,
                'relationship' => 'father',
                'is_primary' => true,
                'start_date' => now(),
            ]);
        }
        echo "✅ Tutor creado\n";

        // 5. DISPOSITIVO
        $device = Device::where('uid', 'RFID-001')->first() ?: Device::create([
            'school_id' => $school->id,
            'uid' => 'RFID-001',
            'type' => 'rfid',
            'status' => 'active',
        ]);
        echo "✅ Dispositivo: {$device->uid}\n";

        // 6. ASIGNACIÓN (CORREGIDO: 'note' no 'notes')
        if (!DeviceAssignment::where('device_id', $device->id)->whereNull('unassigned_at')->exists()) {
            DeviceAssignment::create([
                'device_id' => $device->id,
                'student_id' => $student->id,
                'assigned_at' => now(),
                'note' => 'Asignación inicial', // <- CORRECCIÓN AQUÍ
            ]);
            echo "✅ Dispositivo asignado\n";
        }

        // 7. API CLIENT
        $secret = 'test123';
        $keyId = 'test-key-' . time();

        ApiClient::where('key_id', 'like', 'test-key-%')->delete();

        ApiClient::create([
            'school_id' => $school->id,
            'name' => 'Test Gateway',
            'type' => 'gateway',
            'key_id' => $keyId,
            'key_secret_hash' => hash('sha256', $secret),
            'key_secret_enc' => Crypt::encryptString($secret),
            'active' => true,
        ]);

        echo "\n🔑 CREDENCIALES:\n";
        echo "Key ID: {$keyId}\n";
        echo "Secret: {$secret}\n";
        echo "Device: RFID-001\n\n";

        echo "🎯 Ejecuta: curl -X POST http://localhost/api/gateway/device-events \\\n";
        echo "  -H \"X-Api-Key-Id: {$keyId}\" \\\n";
        echo "  -H \"X-Signature: \$(echo -n '{\\\"uid\\\":\\\"RFID-001\\\",\\\"event_type\\\":\\\"check_in\\\",\\\"occurred_at\\\":\\\"" . now()->toISOString() . "\\\"}' | openssl sha256 -hmac '{$secret}')\" \\\n";
        echo "  -d '{\"uid\":\"RFID-001\",\"event_type\":\"check_in\",\"occurred_at\":\"" . now()->toISOString() . "\"}'\n";
    }
}