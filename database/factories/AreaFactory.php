<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Departamento;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Area>
 */
class AreaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombre = fake()->unique()->words(2, true);

        return [
            'departamento_id' => Departamento::factory(),
            'nombre' => Str::title($nombre),
            'slug' => Str::slug($nombre),
            'descripcion' => fake()->sentence(6),
            'icono' => 'grafico',
            'orden' => fake()->numberBetween(0, 20),
        ];
    }
}
