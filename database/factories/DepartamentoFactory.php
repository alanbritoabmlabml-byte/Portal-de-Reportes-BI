<?php

namespace Database\Factories;

use App\Models\Departamento;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Departamento>
 */
class DepartamentoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombre = fake()->unique()->words(2, true);

        return [
            'nombre' => Str::title($nombre),
            'slug' => Str::slug($nombre),
            'descripcion' => fake()->sentence(6),
            'icono' => 'edificio',
            'orden' => fake()->numberBetween(0, 20),
        ];
    }
}
