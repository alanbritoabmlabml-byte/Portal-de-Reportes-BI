<?php

namespace App\Enums;

/**
 * Roles de la aplicación. Se guardan tal cual en users.rol.
 *
 * - Administrador: gestiona usuarios, tarjetas, reportes y quién ve cada uno.
 * - Usuario: entra al menú y ve solo los reportes públicos o los que se le
 *   asignaron expresamente.
 */
enum Rol: string
{
    case Administrador = 'administrador';
    case Usuario = 'usuario';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Administrador => 'Administrador',
            self::Usuario => 'Usuario',
        };
    }

    /**
     * @return array<string, string> valor => etiqueta, para los <select>
     */
    public static function opciones(): array
    {
        return array_combine(
            array_map(fn (self $rol) => $rol->value, self::cases()),
            array_map(fn (self $rol) => $rol->etiqueta(), self::cases()),
        );
    }
}
