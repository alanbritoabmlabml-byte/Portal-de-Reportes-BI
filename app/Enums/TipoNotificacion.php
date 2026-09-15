<?php

namespace App\Enums;

/**
 * Tono de una notificación. Decide el color de la pastilla en la campana.
 */
enum TipoNotificacion: string
{
    case Info = 'info';
    case Exito = 'exito';
    case Alerta = 'alerta';
    case Error = 'error';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Info => 'Información',
            self::Exito => 'Éxito',
            self::Alerta => 'Alerta',
            self::Error => 'Error',
        };
    }

    /**
     * @return array<string, string> valor => etiqueta, para los <select>
     */
    public static function opciones(): array
    {
        return array_combine(
            array_map(fn (self $tipo) => $tipo->value, self::cases()),
            array_map(fn (self $tipo) => $tipo->etiqueta(), self::cases()),
        );
    }
}
