<?php

namespace Database\Factories;

use App\Models\Sesion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sesion>
 */
class SesionFactory extends Factory
{
    protected $model = Sesion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $iniciada = fake()->dateTimeBetween('-30 days', 'now');
        $segundos = fake()->numberBetween(120, 5400);
        $cerrada = (clone $iniciada)->modify("+{$segundos} seconds");

        return [
            'user_id' => User::factory(),
            'session_id' => fake()->uuid(),
            'ip' => fake()->ipv4(),
            'navegador' => fake()->randomElement(['Chrome', 'Edge', 'Firefox', 'Safari']),
            'plataforma' => fake()->randomElement(['Windows', 'Android', 'macOS', 'iOS']),
            'agente' => fake()->userAgent(),
            'iniciada_at' => $iniciada,
            'ultima_at' => $cerrada,
            'cerrada_at' => $cerrada,
            'motivo_cierre' => 'salida',
            'segundos' => $segundos,
        ];
    }

    /** Sesión que sigue abierta ahora mismo. */
    public function abierta(): static
    {
        return $this->state(fn () => [
            'cerrada_at' => null,
            'motivo_cierre' => null,
            'ultima_at' => now(),
            'iniciada_at' => now()->subMinutes(fake()->numberBetween(2, 40)),
        ]);
    }
}
