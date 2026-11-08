<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Lead;
use App\Models\LeadFeedback;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadFeedback>
 */
final class LeadFeedbackFactory extends Factory
{
    protected $model = LeadFeedback::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory()->verloren(),
            'lead_name' => $this->faker->name(),
            'lead_email' => $this->faker->safeEmail(),
            'kenteken' => strtoupper($this->faker->bothify('##-???-#')),
            'message' => $this->faker->sentence(),
        ];
    }
}
