<?php

namespace App\Models;

use Database\Factories\SesionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Una estancia del usuario en el portal: desde que inicia sesión hasta que
 * sale o se le vence. `segundos` se recalcula en cada latido.
 */
class Sesion extends Model
{
    /** @use HasFactory<SesionFactory> */
    use HasFactory;

    protected $table = 'sesiones';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id', 'session_id', 'ip', 'navegador', 'plataforma', 'agente',
        'iniciada_at', 'ultima_at', 'cerrada_at', 'motivo_cierre', 'segundos',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'iniciada_at' => 'datetime',
            'ultima_at' => 'datetime',
            'cerrada_at' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function visitas(): HasMany
    {
        return $this->hasMany(Visita::class)->orderBy('entrada_at');
    }

    public function asientos(): HasMany
    {
        return $this->hasMany(Bitacora::class);
    }

    public function abierta(): bool
    {
        return $this->cerrada_at === null;
    }

    /** Duración en segundos, ya cerrada o en curso. */
    public function duracion(): int
    {
        return (int) $this->iniciada_at->diffInSeconds($this->cerrada_at ?? $this->ultima_at);
    }

    /** Duración legible: «1 h 24 min», «8 min», «45 s». */
    public function duracionLegible(): string
    {
        return static::formatear($this->duracion());
    }

    public static function formatear(int $segundos): string
    {
        if ($segundos < 60) {
            return $segundos.' s';
        }

        $minutos = intdiv($segundos, 60);

        if ($minutos < 60) {
            return $minutos.' min';
        }

        $horas = intdiv($minutos, 60);
        $resto = $minutos % 60;

        return $resto > 0 ? "{$horas} h {$resto} min" : "{$horas} h";
    }
}
