<?php

namespace App\Models;

use App\Enums\TipoReporte;
use Database\Factories\ReporteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Reporte extends Model
{
    /** @use HasFactory<ReporteFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = ['area_id', 'titulo', 'descripcion', 'tipo', 'url_iframe', 'orden', 'activo', 'publico'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tipo' => TipoReporte::class,
            'activo' => 'boolean',
            'publico' => 'boolean',
        ];
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /** Usuarios con acceso de vista expreso. */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'reporte_user')->withPivot('created_at');
    }

    /**
     * Limita la consulta a lo que puede ver el usuario dado.
     *
     * @param  Builder<Reporte>  $query
     */
    public function scopeVisiblesPara(Builder $query, User $usuario): void
    {
        $query->where('activo', true);

        if ($usuario->esAdministrador()) {
            return;
        }

        $query->where(function (Builder $q) use ($usuario) {
            $q->where('publico', true)
                ->orWhereHas('usuarios', fn (Builder $u) => $u->whereKey($usuario->getKey()));
        });
    }

    public function puedeVerlo(User $usuario): bool
    {
        if (! $this->activo) {
            return false;
        }

        if ($usuario->esAdministrador() || $this->publico) {
            return true;
        }

        return $this->usuarios()->whereKey($usuario->getKey())->exists();
    }

    /** Marcador que muestra el tablero de muestra en lugar de Power BI. */
    public const DEMO = 'demo';

    public function esDemo(): bool
    {
        return $this->url_iframe === self::DEMO;
    }

    /** URL que va al src del iframe. */
    public function urlIframe(): string
    {
        return $this->esDemo() ? route('demo.reporte', $this) : $this->url_iframe;
    }

    /**
     * Si el administrador pegó el <iframe ...> completo de Power BI en lugar
     * de la URL, se rescata solo el src. Así el campo acepta las dos cosas.
     */
    public static function extraerUrl(string $valor): string
    {
        if (preg_match('/src\s*=\s*["\']([^"\']+)["\']/i', $valor, $m)) {
            return html_entity_decode($m[1]);
        }

        return trim($valor);
    }
}
