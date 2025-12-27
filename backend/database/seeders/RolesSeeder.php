<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        DB::table('roles')->insertOrIgnore([
            ['key' => 'admin', 'name' => 'Admin', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'school_admin', 'name' => 'School Admin', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'operator', 'name' => 'Operator', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'guardian', 'name' => 'Guardian', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'student', 'name' => 'Student', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
