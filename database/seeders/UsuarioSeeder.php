<?php

namespace Database\Seeders;

use App\Enums\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Usuarios de arranque. La clave «password» SOLO sirve en local: la base
 * definitiva de usuarios se cargará al cerrar las versiones.
 */
class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            ['name' => 'Alan Moscoso', 'email' => 'amoscoso@plasticoscarmen.com', 'rol' => Rol::Administrador],
            ['name' => 'Kenneth Tercero', 'email' => 'ktercero@plasticoscarmen.com', 'rol' => Rol::Administrador],
            ['name' => 'Edwin Moscoso', 'email' => 'emoscoso@plasticoscarmen.com', 'rol' => Rol::Usuario],
            ['name' => 'Usuario de Prueba', 'email' => 'usuario.prueba@plasticoscarmen.com', 'rol' => Rol::Usuario],
            ['name' => 'Jefatura RRHH', 'email' => 'rrhh.prueba@plasticoscarmen.com', 'rol' => Rol::Usuario],
            ['name' => 'Supervisor Planta', 'email' => 'planta.prueba@plasticoscarmen.com', 'rol' => Rol::Usuario],
        ];

        foreach ($usuarios as $datos) {
            User::query()->updateOrCreate(
                ['email' => $datos['email']],
                $datos + ['password' => 'password', 'activo' => true],
            );
        }
    }
}
