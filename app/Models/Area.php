<?php

namespace App\Models;

use Database\Factories\AreaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Area extends Model
{
    /** @use HasFactory<AreaFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = ['departamento_id', 'nombre', 'slug', 'descripcion', 'icono', 'orden'];

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function reportes(): HasMany
    {
        return $this->hasMany(Reporte::class)->orderBy('orden')->orderBy('titulo');
    }

    public function favoritos(): MorphMany
    {
        return $this->morphMany(Favorito::class, 'favorito');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
