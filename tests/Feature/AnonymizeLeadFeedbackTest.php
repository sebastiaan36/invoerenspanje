<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\LeadFeedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

final class AnonymizeLeadFeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_anonymizes_personal_data_but_keeps_the_message(): void
    {
        $first = LeadFeedback::factory()->create(['lead_name' => 'Jan de Vries', 'lead_email' => 'jan@example.com', 'kenteken' => 'XR-778-S', 'message' => 'Te duur']);
        $second = LeadFeedback::factory()->create(['lead_name' => 'Jan de Vries', 'lead_email' => 'JAN@example.com', 'message' => 'Nog iets']);
        $other = LeadFeedback::factory()->create(['lead_name' => 'Piet', 'lead_email' => 'piet@example.com', 'kenteken' => '12-ABC-3']);

        $this->artisan('leads:anonymize-feedback', ['email' => 'Jan@Example.com'])
            ->expectsOutputToContain('2 feedbackbericht(en) geanonimiseerd')
            ->assertSuccessful();

        foreach ([$first, $second] as $feedback) {
            $feedback->refresh();
            $this->assertSame(LeadFeedback::ANONYMIZED_NAME, $feedback->lead_name);
            $this->assertStringNotContainsString('jan', strtolower($feedback->lead_email));
            $this->assertNull($feedback->kenteken);
        }
        $this->assertSame('Te duur', $first->message);
        $this->assertSame('Nog iets', $second->message);
        $this->assertSame('Piet', $other->fresh()->lead_name);
        $this->assertSame('12-ABC-3', $other->fresh()->kenteken);
    }

    public function test_unknown_email_changes_nothing(): void
    {
        $feedback = LeadFeedback::factory()->create(['lead_email' => 'jan@example.com']);

        $this->artisan('leads:anonymize-feedback', ['email' => 'onbekend@example.com'])
            ->expectsOutputToContain('Geen feedback gevonden')
            ->assertSuccessful();

        $this->assertSame('jan@example.com', $feedback->fresh()->lead_email);
    }

    public function test_invalid_email_is_rejected(): void
    {
        $this->artisan('leads:anonymize-feedback', ['email' => 'geen-adres'])->assertFailed();
    }

    public function test_the_message_stays_immutable_after_anonymizing(): void
    {
        $feedback = LeadFeedback::factory()->create(['lead_email' => 'jan@example.com', 'message' => 'Origineel']);
        LeadFeedback::anonymizeByEmail('jan@example.com');

        try {
            $feedback->refresh()->update(['message' => 'Aangepast']);
            $this->fail('Het bericht mocht niet gewijzigd kunnen worden.');
        } catch (LogicException) {
            // verwacht
        }

        try {
            $feedback->refresh()->update(['lead_name' => 'Weer terug']);
            $this->fail('Buiten anonimiseren mag ook de naam niet wijzigen.');
        } catch (LogicException) {
            // verwacht
        }

        $this->assertSame('Origineel', $feedback->fresh()->message);
        $this->assertSame(LeadFeedback::ANONYMIZED_NAME, $feedback->fresh()->lead_name);
    }
}
