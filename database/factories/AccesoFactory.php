<?php

namespace Database\Factories;

use App\Models\Acceso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Acceso>
 */
class AccesoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->company(),
            'descripcion' => fake()->sentence(8),
            'url' => fake()->url(),
            'texto_boton' => 'Ingresar',
            'imagen' => null,
            'tono' => fake()->randomElement(Acceso::TONOS),
            'etiqueta' => fake()->randomElement(['Interno', 'Público', 'Microsoft 365']),
            'nueva_pestana' => true,
            'destacado' => false,
            'orden' => fake()->numberBetween(0, 20),
            'activo' => true,
        ];
    }

    public function inactivo(): static
    {
        return $this->state(fn () => ['activo' => false]);
    }
}
