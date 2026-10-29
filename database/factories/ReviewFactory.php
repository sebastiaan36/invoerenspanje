<?php

namespace Database\Factories;

use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'location' => fake()->randomElement(['Marbella', 'Estepona', 'Fuengirola', 'Nerja', 'Málaga']),
            'rating' => fake()->numberBetween(4, 5),
            'text' => fake()->sentence(18),
            'source' => 'Google',
            'reviewed_on' => fake()->dateTimeBetween('-1 year'),
            'is_published' => false,
            'sort_order' => 0,
        ];
    }

    /**
     * Indicate that the review is shown on the homepage.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
        ]);
    }
}
