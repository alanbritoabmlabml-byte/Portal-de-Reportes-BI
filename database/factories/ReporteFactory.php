<?php

namespace Database\Factories;

use App\Enums\TipoReporte;
use App\Models\Area;
use App\Models\Reporte;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reporte>
 */
class ReporteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'area_id' => Area::factory(),
            'titulo' => fake()->unique()->sentence(3),
            'descripcion' => fake()->sentence(8),
            'tipo' => fake()->randomElement(TipoReporte::cases()),
            'url_iframe' => 'https://app.powerbi.com/view?r='.fake()->regexify('[A-Za-z0-9]{40}'),
            'orden' => fake()->numberBetween(0, 20),
            'activo' => true,
            'publico' => false,
        ];
    }

    public function publico(): static
    {
        return $this->state(fn () => ['publico' => true]);
    }

    public function inactivo(): static
    {
        return $this->state(fn () => ['activo' => false]);
    }
}
