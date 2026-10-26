<?php

declare(strict_types=1);

namespace Tests\Feature\Portal;

use App\Models\Document;
use App\Models\Dossier;
use App\Models\DossierMessage;
use App\Models\DossierMessageAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The portal is the only place where one customer's data must never reach
 * another customer. Every action is checked against the dossier owner.
 */
final class PortalAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Bus::fake();
    }

    public function test_owner_can_view_their_dossier_pages(): void
    {
        $dossier = Dossier::factory()->create();

        $this->actingAs($dossier->user)->get(route('portaal.dossiers.show', $dossier))->assertOk();
        $this->actingAs($dossier->user)->get(route('portaal.dossiers.documents', $dossier))->assertOk();
        $this->actingAs($dossier->user)->get(route('portaal.dossiers.messages', $dossier))->assertOk();
    }

    public function test_customer_cannot_view_another_customers_dossier_pages(): void
    {
        $dossier = Dossier::factory()->create();
        $intruder = User::factory()->create();

        $this->actingAs($intruder)->get(route('portaal.dossiers.show', $dossier))->assertForbidden();
        $this->actingAs($intruder)->get(route('portaal.dossiers.documents', $dossier))->assertForbidden();
        $this->actingAs($intruder)->get(route('portaal.dossiers.messages', $dossier))->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $dossier = Dossier::factory()->create();

        $this->get(route('portaal.dossiers.show', $dossier))->assertRedirect(route('login'));
    }

    public function test_customer_cannot_upload_to_or_message_another_customers_dossier(): void
    {
        $dossier = Dossier::factory()->create();
        $intruder = User::factory()->create();

        $this->actingAs($intruder)->post(route('portaal.dossiers.documents.store', $dossier), [
            'type' => 'paspoort',
            'file' => UploadedFile::fake()->create('paspoort.pdf', 100, 'application/pdf'),
        ])->assertForbidden();

        $this->actingAs($intruder)->post(route('portaal.dossiers.messages.store', $dossier), [
            'body' => 'Hallo',
        ])->assertForbidden();

        $this->assertDatabaseCount('documents', 0);
        $this->assertDatabaseCount('dossier_messages', 0);
    }

    public function test_owner_upload_is_stored_privately_under_the_detected_extension(): void
    {
        $dossier = Dossier::factory()->create();

        $this->actingAs($dossier->user)->post(route('portaal.dossiers.documents.store', $dossier), [
            'type' => 'paspoort',
            'file' => UploadedFile::fake()->image('paspoort.php.png'),
        ])->assertRedirect();

        $document = Document::sole();
        $this->assertSame('paspoort.php.png', $document->filename);
        $this->assertStringEndsWith('.png', $document->path);
        Storage::disk('local')->assertExists($document->path);
    }

    public function test_upload_with_a_disallowed_file_type_is_rejected(): void
    {
        $dossier = Dossier::factory()->create();

        $this->actingAs($dossier->user)->post(route('portaal.dossiers.documents.store', $dossier), [
            'type' => 'paspoort',
            'file' => UploadedFile::fake()->create('script.html', 10, 'text/html'),
        ])->assertSessionHasErrors('file');

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_document_download_is_limited_to_owner_and_admin(): void
    {
        $document = Document::factory()->create();
        Storage::disk('local')->put($document->path, 'pdf-bytes');

        $this->actingAs(User::factory()->create())
            ->get(route('portaal.documents.download', $document))
            ->assertForbidden();

        $this->actingAs($document->dossier->user)
            ->get(route('portaal.documents.download', $document))
            ->assertOk();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('portaal.documents.download', $document))
            ->assertOk();
    }

    public function test_attachment_download_is_limited_to_owner_and_admin(): void
    {
        $attachment = DossierMessageAttachment::factory()->create(['filename' => 'foto "1".jpg']);
        Storage::disk('local')->put($attachment->path, 'jpg-bytes');
        $owner = $attachment->message->dossier->user;

        $this->actingAs(User::factory()->create())
            ->get(route('portaal.messages.attachments.download', $attachment))
            ->assertForbidden();

        $response = $this->actingAs($owner)
            ->get(route('portaal.messages.attachments.download', $attachment))
            ->assertOk();
        $this->assertStringStartsWith('inline;', (string) $response->headers->get('Content-Disposition'));

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('portaal.messages.attachments.download', $attachment))
            ->assertOk();
    }

    public function test_customer_cannot_delete_another_customers_document_through_their_own_dossier(): void
    {
        $ownDossier = Dossier::factory()->create();
        $foreignDocument = Document::factory()->create();

        $this->actingAs($ownDossier->user)
            ->delete(route('portaal.dossiers.documents.destroy', [$ownDossier, $foreignDocument]))
            ->assertForbidden();

        $this->assertModelExists($foreignDocument);
    }

    public function test_owner_can_delete_an_uploaded_document_but_not_a_reviewed_one(): void
    {
        $dossier = Dossier::factory()->create();
        $uploaded = Document::factory()->for($dossier)->create();
        $approved = Document::factory()->for($dossier)->approved()->create();

        $this->actingAs($dossier->user)
            ->delete(route('portaal.dossiers.documents.destroy', [$dossier, $approved]))
            ->assertForbidden();
        $this->assertModelExists($approved);

        $this->actingAs($dossier->user)
            ->delete(route('portaal.dossiers.documents.destroy', [$dossier, $uploaded]))
            ->assertRedirect();
        $this->assertModelMissing($uploaded);
    }

    public function test_owner_can_send_a_message_with_an_attachment(): void
    {
        $dossier = Dossier::factory()->create();

        $this->actingAs($dossier->user)->post(route('portaal.dossiers.messages.store', $dossier), [
            'body' => 'Hier is de foto.',
            'attachments' => [UploadedFile::fake()->image('auto.jpg')],
        ])->assertRedirect();

        $message = DossierMessage::sole();
        $this->assertSame('klant', $message->author_role);
        $attachment = $message->attachments()->sole();
        $this->assertStringEndsWith('.jpg', $attachment->path);
        Storage::disk('local')->assertExists($attachment->path);
    }

    public function test_admin_is_redirected_out_of_the_customer_portal(): void
    {
        $dossier = Dossier::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('portaal.dossiers.show', $dossier))
            ->assertRedirect('/admin');
    }
}
