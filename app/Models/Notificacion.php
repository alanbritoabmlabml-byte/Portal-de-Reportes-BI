<?php

namespace App\Models;

use App\Enums\TipoNotificacion;
use Database\Factories\NotificacionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Notificacion extends Model
{
    /** @use HasFactory<NotificacionFactory> */
    use HasFactory;

    protected $table = 'notificaciones';

    /**
     * @var list<string>
     */
    protected $fillable = ['user_id', 'titulo', 'mensaje', 'tipo', 'url', 'leida_at'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tipo' => TipoNotificacion::class,
            'leida_at' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function leida(): bool
    {
        return $this->leida_at !== null;
    }

    /** Crea la misma notificación para cada usuario de la colección. */
    public static function avisar(iterable $usuarios, string $titulo, ?string $mensaje = null, TipoNotificacion $tipo = TipoNotificacion::Info, ?string $url = null): void
    {
        $ahora = now();
        $filas = Collection::make($usuarios)
            ->map(fn (User $u) => [
                'user_id' => $u->getKey(),
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'tipo' => $tipo->value,
                'url' => $url,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ])
            ->all();

        if ($filas !== []) {
            static::insert($filas);
        }
    }
}
