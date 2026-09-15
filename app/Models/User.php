<?php

namespace App\Models;

use App\Enums\Rol;
use App\Enums\Tema;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'activo',
        'tema',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'rol' => Rol::class,
            'tema' => Tema::class,
            'activo' => 'boolean',
        ];
    }

    public function esAdministrador(): bool
    {
        return $this->rol === Rol::Administrador;
    }

    /** Reportes a los que se le dio acceso de vista expresamente. */
    public function reportes(): BelongsToMany
    {
        return $this->belongsToMany(Reporte::class, 'reporte_user')->withPivot('created_at');
    }

    public function favoritos(): HasMany
    {
        return $this->hasMany(Favorito::class);
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class)->latest();
    }

    public function notificacionesSinLeer(): HasMany
    {
        return $this->notificaciones()->whereNull('leida_at');
    }

    /**
     * Reportes activos que este usuario puede ver: todos si administra,
     * los públicos más los asignados si no.
     *
     * @return Builder<Reporte>
     */
    public function reportesVisibles(): Builder
    {
        $consulta = Reporte::query()->where('activo', true);

        if ($this->esAdministrador()) {
            return $consulta;
        }

        return $consulta->where(function (Builder $q) {
            $q->where('publico', true)
                ->orWhereHas('usuarios', fn (Builder $u) => $u->whereKey($this->getKey()));
        });
    }

    /** Primer nombre para el saludo del menú. */
    public function nombreCorto(): string
    {
        return explode(' ', trim($this->name))[0];
    }

    /** Iniciales para el avatar (máximo dos letras). */
    public function iniciales(): string
    {
        $partes = preg_split('/\s+/', trim($this->name)) ?: [];
        $iniciales = mb_substr($partes[0] ?? '', 0, 1);

        if (count($partes) > 1) {
            $iniciales .= mb_substr(end($partes), 0, 1);
        }

        return mb_strtoupper($iniciales);
    }

    /** Tope de elementos que un usuario puede anclar. */
    public const MAX_FAVORITOS = 6;

    public function tieneFavorito(Acceso|Area $elemento): bool
    {
        return $this->favoritos
            ->contains(fn (Favorito $f) => $f->favorito_type === $elemento->getMorphClass()
                && (int) $f->favorito_id === (int) $elemento->getKey());
    }
}
