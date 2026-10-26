<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Dossier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
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
            'type' => 'paspoort',
            'filename' => 'paspoort.pdf',
            'path' => 'dossiers/'.fake()->uuid().'.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'status' => Document::STATUS_GEUPLOAD,
        ];
    }

    /**
     * Indicate that an admin already approved the document.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Document::STATUS_GOEDGEKEURD,
        ]);
    }
}
