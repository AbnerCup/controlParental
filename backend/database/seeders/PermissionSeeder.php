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
        // 1. Definir Permisos (exactamente como los tenías)
        $permissions = [
            // --- GESTIÓN DE UNIDADES EDUCATIVAS ---
            [
                'key' => 'schools.manage',
                'name' => 'Gestión Total de Escuelas',
                'description' => 'Permite crear, editar, configurar y dar de baja unidades educativas en el sistema central.'
            ],
            [
                'key' => 'schools.view',
                'name' => 'Solo Ver Escuelas',
                'description' => 'Permite visualizar la lista de escuelas y sus detalles básicos sin privilegios de modificación.'
            ],

            // --- ADMINISTRACIÓN DE USUARIOS ---
            [
                'key' => 'users.manage',
                'name' => 'Gestión de Personal Administrativo',
                'description' => 'Administrar cuentas de usuario, asignar roles y permisos al personal administrativo y operativo.'
            ],

            // --- CONTROL ACADÉMICO ---
            [
                'key' => 'students.manage',
                'name' => 'Gestión de Alumnos',
                'description' => 'Registrar, editar y gestionar la información personal y académica de los estudiantes.'
            ],
            [
                'key' => 'grades.manage',
                'name' => 'Gestión de Cursos y Grados',
                'description' => 'Configurar los niveles académicos, paralelos y la organización de grados de la institución.'
            ],

            // --- HARDWARE, RFID & SEGURIDAD ---
            [
                'key' => 'devices.manage',
                'name' => 'Gestión de Dispositivos y Tags',
                'description' => 'Configurar Gateways, lectores RFID y administrar el inventario de tarjetas/tags disponibles.'
            ],
            [
                'key' => 'devices.assign',
                'name' => 'Vincular/Desvincular Tags a Alumnos',
                'description' => 'Asociar físicamente un tag RFID a un estudiante específico para el control de acceso.'
            ],

            // --- MONITOREO Y ALERTAS ---
            [
                'key' => 'attendance.view',
                'name' => 'Ver Reportes de Asistencia Real-Time',
                'description' => 'Acceso al monitor en vivo de ingresos y salidas procesados por los lectores RFID.'
            ],
            [
                'key' => 'panics.manage',
                'name' => 'Gestión y Monitoreo de Alertas de Pánico',
                'description' => 'Recibir y gestionar notificaciones críticas de emergencia activadas por los dispositivos.'
            ],

            // --- REPORTES Y NOTIFICACIONES ---
            [
                'key' => 'reports.view',
                'name' => 'Ver y Exportar Reportes',
                'description' => 'Generar reportes históricos de asistencia y comportamiento para exportar a PDF o Excel.'
            ],
            [
                'key' => 'notifications.setup',
                'name' => 'Configurar Alertas SMS/Email',
                'description' => 'Configurar los canales de comunicación y los contactos de emergencia para el envío automático de alertas.'
            ],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(
                ['key' => $p['key']], // Lo busca por la llave
                [
                    'name' => $p['name'],
                    'description' => $p['description']
                ]
            );
        }

        // 2. Crear o buscar el Rol Super Admin
        $adminRole = Role::firstOrCreate(
            ['key' => 'super_admin'],
            ['name' => 'Super Administrador del Sistema']
        );

        // 3. Asignar TODOS los permisos al rol Super Admin
        $allPermissionIds = Permission::all()->pluck('id');
        $adminRole->permissions()->sync($allPermissionIds);

        // 4. ASIGNAR EL ROL AL USUARIO ESPECÍFICO (El ID 17)
        $user = \App\Models\User::where('email', 'super_admin@demo.local')->first();

        if ($user) {
            // sync garantiza que tenga el rol sin duplicarlo
            $user->roles()->sync([$adminRole->id]);
            $this->command->info("Rol super_admin asignado a: {$user->email}");
        } else {
            $this->command->error("No se encontró el usuario super_admin@demo.local");
        }
    }
}
