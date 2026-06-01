<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'nombre_role' => 'Administrador',
                'descripcion' => 'Acceso total al sistema',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre_role' => 'Asesor',
                'descripcion' => 'Solo acceso al multichat',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}