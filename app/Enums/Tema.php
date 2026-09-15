<?php

namespace App\Enums;

/**
 * Preferencia de apariencia del portal. «Sistema» sigue la configuración del
 * equipo del usuario; las otras dos la fuerzan.
 */
enum Tema: string
{
    case Sistema = 'sistema';
    case Claro = 'claro';
    case Oscuro = 'oscuro';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Sistema => 'Según el sistema',
            self::Claro => 'Claro',
            self::Oscuro => 'Oscuro',
        };
    }

    /** Siguiente valor del ciclo del botón de la barra: claro → oscuro → sistema. */
    public function siguiente(): self
    {
        return match ($this) {
            self::Claro => self::Oscuro,
            self::Oscuro => self::Sistema,
            self::Sistema => self::Claro,
        };
    }

    /**
     * @return array<string, string> valor => etiqueta, para los <select>
     */
    public static function opciones(): array
    {
        return array_combine(
            array_map(fn (self $t) => $t->value, self::cases()),
            array_map(fn (self $t) => $t->etiqueta(), self::cases()),
        );
    }
}
