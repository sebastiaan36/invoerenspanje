<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
final class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'woonplaats_spanje' => $this->faker->city(),
            'kenteken' => strtoupper($this->faker->bothify('##-???-#')),
            'package_slug' => 'compleet',
            'residency_change' => false,
            'autonomia' => 'Comunidad Valenciana',
            'source' => 'offerte_formulier',
            'status' => 'nieuw',
        ];
    }

    public function gewonnen(): self
    {
        return $this->state(fn (array $attributes): array => ['status' => 'gewonnen']);
    }
}
