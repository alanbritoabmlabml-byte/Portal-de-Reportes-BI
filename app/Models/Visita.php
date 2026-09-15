<?php

namespace App\Models;

use Database\Factories\VisitaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Paso del usuario por una página: cuándo entró, cuándo la dejó y cuántos
 * segundos estuvo. La cierra la visita siguiente o el latido del navegador.
 */
class Visita extends Model
{
    /** @use HasFactory<VisitaFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'sesion_id', 'user_id', 'ruta', 'url', 'titulo',
        'entrada_at', 'salida_at', 'segundos',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'entrada_at' => 'datetime',
            'salida_at' => 'datetime',
        ];
    }

    public function sesion(): BelongsTo
    {
        return $this->belongsTo(Sesion::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function permanenciaLegible(): string
    {
        return Sesion::formatear($this->segundos);
    }
}
