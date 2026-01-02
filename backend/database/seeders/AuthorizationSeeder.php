<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AuthorizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear el Permiso
        Permission::firstOrCreate([
            'key' => 'schools.view',
            'name' => 'Ver Unidades Educativas'
        ]);
    }
}
