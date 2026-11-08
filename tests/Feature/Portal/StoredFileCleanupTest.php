<?php

declare(strict_types=1);

namespace Tests\Feature\Portal;

use App\Models\Document;
use App\Models\Dossier;
use App\Models\DossierMessage;
use App\Models\DossierMessageAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class StoredFileCleanupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_deleting_a_document_removes_its_file(): void
    {
        $document = $this->documentWithFile();

        $document->delete();

        Storage::disk('local')->assertMissing($document->path);
    }

    public function test_deleting_an_attachment_removes_its_file(): void
    {
        $attachment = $this->attachmentWithFile();

        $attachment->delete();

        Storage::disk('local')->assertMissing($attachment->path);
    }

    public function test_deleting_a_message_removes_its_attachment_files(): void
    {
        $attachment = $this->attachmentWithFile();

        $attachment->message->delete();

        $this->assertModelMissing($attachment);
        Storage::disk('local')->assertMissing($attachment->path);
    }

    public function test_deleting_a_dossier_removes_all_its_files_and_folders(): void
    {
        $dossier = Dossier::factory()->create();
        $document = $this->documentWithFile($dossier);
        $attachment = $this->attachmentWithFile($dossier);
        Storage::disk('local')->put("dossiers/{$dossier->id}/los-bestand.pdf", 'x');

        $otherDocument = $this->documentWithFile();

        $dossier->delete();

        $this->assertModelMissing($document);
        $this->assertModelMissing($attachment);
        Storage::disk('local')->assertMissing($document->path);
        Storage::disk('local')->assertMissing($attachment->path);
        Storage::disk('local')->assertMissing("dossiers/{$dossier->id}");
        Storage::disk('local')->assertMissing("dossier-message-attachments/{$dossier->id}");

        Storage::disk('local')->assertExists($otherDocument->path);
    }

    public function test_customer_deleting_a_document_in_the_portal_removes_its_file(): void
    {
        $user = User::factory()->create(['role' => 'klant']);
        $dossier = Dossier::factory()->create(['user_id' => $user->id]);
        $document = $this->documentWithFile($dossier);

        $this->actingAs($user)
            ->delete(route('portaal.dossiers.documents.destroy', [$dossier, $document]))
            ->assertRedirect();

        $this->assertModelMissing($document);
        Storage::disk('local')->assertMissing($document->path);
    }

    private function documentWithFile(?Dossier $dossier = null): Document
    {
        $dossier ??= Dossier::factory()->create();
        $path = "dossiers/{$dossier->id}/paspoort-".uniqid().'.pdf';
        Storage::disk('local')->put($path, 'pdf');

        return Document::factory()->create(['dossier_id' => $dossier->id, 'path' => $path]);
    }

    private function attachmentWithFile(?Dossier $dossier = null): DossierMessageAttachment
    {
        $dossier ??= Dossier::factory()->create();
        $message = DossierMessage::factory()->create(['dossier_id' => $dossier->id]);
        $path = "dossier-message-attachments/{$dossier->id}/foto-".uniqid().'.jpg';
        Storage::disk('local')->put($path, 'jpg');

        return DossierMessageAttachment::factory()->create(['dossier_message_id' => $message->id, 'path' => $path]);
    }
}
