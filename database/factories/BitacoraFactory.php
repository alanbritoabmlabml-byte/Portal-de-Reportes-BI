<?php

namespace Database\Factories;

use App\Enums\AccionBitacora;
use App\Models\Bitacora;
use App\Models\Sesion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bitacora>
 */
class BitacoraFactory extends Factory
{
    protected $model = Bitacora::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sesion_id' => Sesion::factory(),
            'user_id' => fn (array $atributos) => Sesion::find($atributos['sesion_id'])?->user_id,
            'usuario_nombre' => fake()->name(),
            'accion' => fake()->randomElement(AccionBitacora::cases()),
            'entidad' => 'Reporte',
            'entidad_id' => fake()->numberBetween(1, 30),
            'descripcion' => fake()->sentence(6),
            'datos' => null,
            'ruta' => 'admin.reportes.index',
            'ip' => fake()->ipv4(),
        ];
    }
}
