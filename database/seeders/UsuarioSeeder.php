<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $adminRoleId = DB::table('roles')->where('nombre_role', 'Administrador')->value('id_rol');
        $asesorRoleId = DB::table('roles')->where('nombre_role', 'Asesor')->value('id_rol');

        Usuario::create([
            'nombre' => 'Administrador',
            'email' => 'admin@donguando.com',
            'username' => 'admin',
            'password' => 'admin123',
            'id_rol' => $adminRoleId,
            'activo' => 1,
        ]);

        Usuario::create([
            'nombre' => 'Asesor Principal',
            'email' => 'asesor@donguando.com',
            'username' => 'asesor',
            'password' => 'asesor123', 
            'id_rol' => $asesorRoleId,
            'activo' => 1,
        ]);
    }
}