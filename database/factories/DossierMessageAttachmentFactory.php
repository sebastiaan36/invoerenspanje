<?php

namespace Database\Factories;

use App\Models\DossierMessage;
use App\Models\DossierMessageAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DossierMessageAttachment>
 */
class DossierMessageAttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dossier_message_id' => DossierMessage::factory(),
            'filename' => 'foto.jpg',
            'path' => 'dossier-message-attachments/'.fake()->uuid().'.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 2048,
        ];
    }
}
