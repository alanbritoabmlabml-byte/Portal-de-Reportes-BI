<?php

namespace App\Enums;

/**
 * Tipos de asiento de la bitácora. El color de la pastilla sale de aquí.
 */
enum AccionBitacora: string
{
    case Entrar = 'entrar';
    case Salir = 'salir';
    case Ver = 'ver';
    case Abrir = 'abrir';
    case Crear = 'crear';
    case Editar = 'editar';
    case Eliminar = 'eliminar';
    case Acceso = 'acceso';
    case Exportar = 'exportar';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Entrar => 'Inicio de sesión',
            self::Salir => 'Cierre de sesión',
            self::Ver => 'Consulta',
            self::Abrir => 'Apertura de enlace',
            self::Crear => 'Alta',
            self::Editar => 'Modificación',
            self::Eliminar => 'Baja',
            self::Acceso => 'Cambio de accesos',
            self::Exportar => 'Exportación',
        };
    }

    /** Clase de color de la pastilla en la tabla de bitácora. */
    public function tono(): string
    {
        return match ($this) {
            self::Entrar, self::Crear => 'verde',
            self::Salir, self::Eliminar => 'rojo',
            self::Editar, self::Acceso => 'ambar',
            self::Exportar => 'tinta',
            default => 'neutro',
        };
    }

    /**
     * @return array<string, string> valor => etiqueta, para los <select>
     */
    public static function opciones(): array
    {
        return array_combine(
            array_map(fn (self $a) => $a->value, self::cases()),
            array_map(fn (self $a) => $a->etiqueta(), self::cases()),
        );
    }
}
