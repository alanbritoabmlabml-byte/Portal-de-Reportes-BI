<?php

namespace Database\Factories;

use App\Enums\TipoNotificacion;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notificacion>
 */
class NotificacionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'titulo' => fake()->sentence(4),
            'mensaje' => fake()->sentence(10),
            'tipo' => fake()->randomElement(TipoNotificacion::cases()),
            'url' => null,
            'leida_at' => null,
        ];
    }

    public function leida(): static
    {
        return $this->state(fn () => ['leida_at' => now()]);
    }
}
