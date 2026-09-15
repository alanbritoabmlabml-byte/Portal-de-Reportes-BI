<?php

namespace App\Models;

use App\Enums\GrupoAcceso;
use Database\Factories\AccesoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

/**
 * Tarjeta del menú principal que lleva a otro sitio (portafolio, SharePoint,
 * sitios públicos...). La tarjeta de Reportes BI no vive aquí: se arma sola
 * a partir de departamentos y áreas.
 */
class Acceso extends Model
{
    /** @use HasFactory<AccesoFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nombre', 'descripcion', 'url', 'texto_boton', 'imagen', 'tono',
        'etiqueta', 'grupo', 'nueva_pestana', 'destacado', 'orden', 'activo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'grupo' => GrupoAcceso::class,
            'nueva_pestana' => 'boolean',
            'destacado' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    /** @var list<string> */
    public const TONOS = ['azul', 'rojo', 'tinta', 'claro'];

    public function favoritos(): MorphMany
    {
        return $this->morphMany(Favorito::class, 'favorito');
    }

    /** URL lista para el atributo src/href: acepta ruta bajo public/ o absoluta. */
    public function urlImagen(): ?string
    {
        if (! $this->imagen) {
            return null;
        }

        return Str::startsWith($this->imagen, ['http://', 'https://', '//'])
            ? $this->imagen
            : asset($this->imagen);
    }

    /** Dominio legible para mostrar bajo el nombre (sin protocolo ni ruta). */
    public function dominio(): string
    {
        $host = parse_url($this->url, PHP_URL_HOST) ?: $this->url;

        return Str::after($host, 'www.');
    }
}
