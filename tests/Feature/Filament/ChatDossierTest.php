<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\Dossiers\Pages\ChatDossier;
use App\Models\Dossier;
use App\Models\DossierMessageAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

final class ChatDossierTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_attachment_is_stored_with_the_extension_of_its_detected_mime_type(): void
    {
        Storage::fake('local');
        $this->actingAs(User::factory()->admin()->create());
        $dossier = Dossier::factory()->create();

        Livewire::test(ChatDossier::class, ['record' => $dossier->getRouteKey()])
            ->fillForm([
                'reply' => 'Zie bijlage.',
                'attachments' => [UploadedFile::fake()->create('scan.jpg', 10, 'application/pdf')],
            ])
            ->call('send')
            ->assertHasNoFormErrors();

        $attachment = DossierMessageAttachment::sole();

        $this->assertSame('scan.jpg', $attachment->filename);
        $this->assertStringEndsWith('.pdf', $attachment->path);
        $this->assertStringStartsWith("dossier-message-attachments/{$dossier->id}/", $attachment->path);
        Storage::disk('local')->assertExists($attachment->path);
    }
}
