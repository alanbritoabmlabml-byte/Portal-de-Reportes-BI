<?php

namespace App\Enums;

/**
 * Clasificación de los reportes de Power BI por el nivel al que sirven.
 * Es la lista que pidió Dirección: dirección, gerenciales, operativos,
 * control, supervisión, planificación y asistencias.
 */
enum TipoReporte: string
{
    case Direccion = 'direccion';
    case Gerencial = 'gerencial';
    case Operativo = 'operativo';
    case Control = 'control';
    case Supervision = 'supervision';
    case Planificacion = 'planificacion';
    case Asistencia = 'asistencia';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Direccion => 'Dirección',
            self::Gerencial => 'Gerencial',
            self::Operativo => 'Operativo',
            self::Control => 'Control',
            self::Supervision => 'Supervisión',
            self::Planificacion => 'Planificación',
            self::Asistencia => 'Asistencias',
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
