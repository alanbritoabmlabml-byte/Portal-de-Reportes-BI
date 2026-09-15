<?php

namespace App\Models;

use App\Enums\AccionBitacora;
use Database\Factories\BitacoraFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Asiento de bitácora: una cosa que pasó, con quién la hizo y sobre qué.
 */
class Bitacora extends Model
{
    /** @use HasFactory<BitacoraFactory> */
    use HasFactory;

    protected $table = 'bitacora';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'sesion_id', 'user_id', 'usuario_nombre', 'accion', 'entidad',
        'entidad_id', 'descripcion', 'datos', 'ruta', 'ip',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'accion' => AccionBitacora::class,
            'datos' => 'array',
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
}
