<?php

namespace Database\Factories;

use App\Models\Dossier;
use App\Models\DossierMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DossierMessage>
 */
class DossierMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dossier_id' => Dossier::factory(),
            'author_id' => fn (array $attributes) => Dossier::findOrFail($attributes['dossier_id'])->user_id,
            'author_role' => 'klant',
            'body' => fake()->sentence(),
        ];
    }
}
