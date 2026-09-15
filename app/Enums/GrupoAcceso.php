<?php

namespace App\Enums;

/**
 * Mosaico del menú en el que aparece cada tarjeta de acceso.
 */
enum GrupoAcceso: string
{
    case Propio = 'propio';
    case SharePoint = 'sharepoint';
    case Publico = 'publico';
    case Microsoft = 'microsoft';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Propio => 'Desarrollos propios',
            self::SharePoint => 'SharePoint',
            self::Publico => 'Sitios públicos',
            self::Microsoft => 'Microsoft 365',
        };
    }

    public function bajada(): string
    {
        return match ($this) {
            self::Propio => 'Sistemas construidos por el área de Sistemas',
            self::SharePoint => 'Sitios documentales del Directorio y las áreas',
            self::Publico => 'Páginas institucionales abiertas al público',
            self::Microsoft => 'Correo, Teams, archivos y servicio de Power BI',
        };
    }

    public function icono(): string
    {
        return match ($this) {
            self::Propio => 'chip',
            self::SharePoint => 'cajas',
            self::Publico => 'tienda',
            self::Microsoft => 'enlace',
        };
    }

    /**
     * @return array<string, string> valor => etiqueta, para los <select>
     */
    public static function opciones(): array
    {
        return array_combine(
            array_map(fn (self $g) => $g->value, self::cases()),
            array_map(fn (self $g) => $g->etiqueta(), self::cases()),
        );
    }
}
