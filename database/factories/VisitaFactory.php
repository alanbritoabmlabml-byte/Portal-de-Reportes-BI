<?php

namespace Database\Factories;

use App\Models\Sesion;
use App\Models\Visita;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visita>
 */
class VisitaFactory extends Factory
{
    protected $model = Visita::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $entrada = fake()->dateTimeBetween('-30 days', 'now');
        $segundos = fake()->numberBetween(15, 900);

        return [
            'sesion_id' => Sesion::factory(),
            'user_id' => fn (array $atributos) => Sesion::find($atributos['sesion_id'])?->user_id,
            'ruta' => 'menu',
            'url' => fake()->url(),
            'titulo' => fake()->randomElement(['Menú principal', 'Tableros · RRHH', 'Notificaciones', 'Administrar reportes']),
            'entrada_at' => $entrada,
            'salida_at' => (clone $entrada)->modify("+{$segundos} seconds"),
            'segundos' => $segundos,
        ];
    }
}
