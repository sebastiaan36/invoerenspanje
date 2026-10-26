<?php

namespace Database\Factories;

use App\Models\Dossier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dossier>
 */
class DossierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => Dossier::STATUS_AKKOORD,
            'kenteken' => strtoupper(fake()->bothify('##???#')),
            'merk' => 'VOLKSWAGEN',
            'model' => 'GOLF',
            'pakket' => 'compleet',
            'started_at' => now(),
        ];
    }
}
