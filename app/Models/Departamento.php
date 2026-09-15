<?php

namespace App\Models;

use Database\Factories\DepartamentoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Departamento extends Model
{
    /** @use HasFactory<DepartamentoFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = ['nombre', 'slug', 'descripcion', 'icono', 'orden'];

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class)->orderBy('orden')->orderBy('nombre');
    }

    public function reportes(): HasManyThrough
    {
        return $this->hasManyThrough(Reporte::class, Area::class);
    }
}
