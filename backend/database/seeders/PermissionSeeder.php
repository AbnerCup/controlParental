<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // --- SISTEMA GLOBAL (Super Admin) ---
            ['key' => 'schools.manage', 'name' => 'Gestión Total de Escuelas'], // Ver, crear, editar, borrar
            ['key' => 'schools.view', 'name' => 'Solo Ver Escuelas'],

            // --- GESTIÓN DE PERSONAL (Super Admin / School Admin) ---
            ['key' => 'users.manage', 'name' => 'Gestión de Personal Administrativo'],

            // --- ACADÉMICO (School Admin / Operador) ---
            ['key' => 'students.manage', 'name' => 'Gestión de Alumnos'],
            ['key' => 'grades.manage', 'name' => 'Gestión de Cursos y Grados'],

            // --- HARDWARE & RFID (El núcleo de tu proyecto) ---
            ['key' => 'devices.manage', 'name' => 'Gestión de Dispositivos y Tags'],
            ['key' => 'devices.assign', 'name' => 'Vincular/Desvincular Tags a Alumnos'],

            // --- SEGURIDAD & ASISTENCIA (Lo que falta) ---
            ['key' => 'attendance.view', 'name' => 'Ver Reportes de Asistencia Real-Time'],
            ['key' => 'panics.manage', 'name' => 'Gestión y Monitoreo de Alertas de Pánico'],

            // --- COMUNICACIÓN & REPORTES ---
            ['key' => 'reports.view', 'name' => 'Ver y Exportar Reportes'],
            ['key' => 'notifications.setup', 'name' => 'Configurar Alertas SMS/Email'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['key' => $p['key']], $p);
        }
        $adminRole = Role::where('key', 'super_admin')->first();
        if ($adminRole) {
            $allPermissions = Permission::all();
            $adminRole->permissions()->sync($allPermissions->pluck('id'));
        }
    }
}
