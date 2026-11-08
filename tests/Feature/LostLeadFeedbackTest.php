<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\LeadFeedbackReceivedNotification;
use App\Mail\LostLeadFeedbackMail;
use App\Models\Lead;
use App\Models\LeadFeedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class LostLeadFeedbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_setting_status_to_lost_records_when_the_lead_was_lost(): void
    {
        $this->freezeTime();
        $lead = Lead::factory()->create();

        $lead->update(['status' => 'verloren']);

        $this->assertSame(now()->timestamp, $lead->fresh()->lost_at->timestamp);
    }

    public function test_leaving_the_lost_status_clears_the_lost_date(): void
    {
        $lead = Lead::factory()->verloren()->create();

        $lead->update(['status' => 'gecontacteerd']);

        $this->assertNull($lead->fresh()->lost_at);
    }

    public function test_saving_other_fields_keeps_the_original_lost_date(): void
    {
        $lead = Lead::factory()->verloren()->create();
        $lead->forceFill(['lost_at' => now()->subDays(20)])->save();

        $lead->update(['comment' => 'Nagebeld']);

        $this->assertTrue($lead->fresh()->lost_at->isSameDay(now()->subDays(20)));
    }

    public function test_command_mails_leads_lost_at_least_two_weeks_ago(): void
    {
        Mail::fake();
        $due = $this->lostLead(daysAgo: 14);
        $tooRecent = $this->lostLead(daysAgo: 13);
        $alreadyAsked = $this->lostLead(daysAgo: 30, attributes: ['feedback_requested_at' => now()->subDays(10)]);
        Lead::factory()->create(['status' => 'offerte']);

        $this->artisan('leads:request-feedback')->assertSuccessful();

        Mail::assertSent(LostLeadFeedbackMail::class, 1);
        Mail::assertSent(LostLeadFeedbackMail::class, fn (LostLeadFeedbackMail $mail) => $mail->hasTo($due->email));
        $this->assertNotNull($due->fresh()->feedback_requested_at);
        $this->assertNotNull($due->fresh()->feedback_token);
        $this->assertNull($tooRecent->fresh()->feedback_requested_at);
        $this->assertTrue($alreadyAsked->fresh()->feedback_requested_at->isSameDay(now()->subDays(10)));
    }

    public function test_command_never_mails_the_same_lead_twice(): void
    {
        Mail::fake();
        $this->lostLead(daysAgo: 20);

        $this->artisan('leads:request-feedback')->assertSuccessful();
        $this->artisan('leads:request-feedback')->assertSuccessful();

        Mail::assertSent(LostLeadFeedbackMail::class, 1);
    }

    public function test_dry_run_sends_nothing(): void
    {
        Mail::fake();
        $lead = $this->lostLead(daysAgo: 20);

        $this->artisan('leads:request-feedback', ['--dry-run' => true])
            ->expectsOutputToContain($lead->email)
            ->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertNull($lead->fresh()->feedback_requested_at);
    }

    public function test_feedback_mail_is_plain_text_from_sebastiaan_with_a_personal_link(): void
    {
        $lead = $this->lostLead(daysAgo: 20, attributes: ['name' => 'Jan de Vries', 'kenteken' => 'XR-778-S']);

        $mail = new LostLeadFeedbackMail($lead);

        $mail->assertFrom('info@autoinvoerenspanje.nl', 'Sebastiaan Been');
        $mail->assertHasReplyTo('info@autoinvoerenspanje.nl');
        $mail->assertSeeInText('Beste Jan de Vries');
        $mail->assertSeeInText('XR-778-S');
        $mail->assertSeeInText(route('feedback.show', ['token' => $lead->fresh()->feedback_token]));
        $this->assertNull($mail->content()->view);
        $this->assertNull($mail->content()->html);
        $this->assertNull($mail->content()->markdown);
    }

    public function test_feedback_page_shows_the_form_for_a_valid_token(): void
    {
        $this->lostLead(daysAgo: 20, attributes: ['name' => 'Jan de Vries', 'feedback_token' => 'geldige-token']);

        $this->get(route('feedback.show', ['token' => 'geldige-token']))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertSee('<title>'.e(config('seo.routes')['feedback.show']['title']).'</title>', false)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Feedback')
                ->where('token', 'geldige-token')
                ->where('firstName', 'Jan')
                ->where('submitted', false));
    }

    public function test_unknown_token_returns_404(): void
    {
        $this->get(route('feedback.show', ['token' => 'bestaat-niet']))->assertNotFound();
        $this->post(route('feedback.store', ['token' => 'bestaat-niet']), ['message' => 'Hoi'])->assertNotFound();
    }

    public function test_submitting_feedback_stores_it_and_notifies_us(): void
    {
        Mail::fake();
        $lead = $this->lostLead(daysAgo: 20, attributes: ['name' => 'Jan de Vries', 'kenteken' => 'XR-778-S', 'feedback_token' => 'geldige-token']);
        $url = route('feedback.show', ['token' => 'geldige-token']);

        $this->from($url)
            ->post(route('feedback.store', ['token' => 'geldige-token']), ['message' => 'De prijs was te hoog.'])
            ->assertRedirect($url);

        $feedback = LeadFeedback::sole();
        $this->assertSame($lead->id, $feedback->lead_id);
        $this->assertSame('Jan de Vries', $feedback->lead_name);
        $this->assertSame($lead->email, $feedback->lead_email);
        $this->assertSame('XR-778-S', $feedback->kenteken);
        $this->assertSame('De prijs was te hoog.', $feedback->message);
        Mail::assertSent(LeadFeedbackReceivedNotification::class, fn (LeadFeedbackReceivedNotification $mail) => $mail->hasTo('info@autoinvoerenspanje.nl')
            && str_contains($mail->render(), 'De prijs was te hoog.'));

        $this->get($url)->assertInertia(fn (Assert $page) => $page->where('submitted', true));
    }

    public function test_a_second_submission_is_added_and_never_overwrites_the_first(): void
    {
        Mail::fake();
        $this->lostLead(daysAgo: 20, attributes: ['feedback_token' => 'geldige-token']);

        $this->post(route('feedback.store', ['token' => 'geldige-token']), ['message' => 'Eerste reactie']);
        $this->post(route('feedback.store', ['token' => 'geldige-token']), ['message' => 'Tweede reactie']);

        $this->assertSame(['Eerste reactie', 'Tweede reactie'], LeadFeedback::orderBy('id')->pluck('message')->all());
    }

    public function test_feedback_cannot_be_deleted(): void
    {
        $feedback = LeadFeedback::factory()->create();

        try {
            $feedback->delete();
            $this->fail('Feedback mocht niet verwijderd kunnen worden.');
        } catch (\LogicException) {
            // verwacht
        }

        $this->assertModelExists($feedback);
    }

    public function test_feedback_cannot_be_changed(): void
    {
        $feedback = LeadFeedback::factory()->create(['message' => 'Origineel']);

        try {
            $feedback->update(['message' => 'Aangepast']);
            $this->fail('Feedback mocht niet gewijzigd kunnen worden.');
        } catch (\LogicException) {
            // verwacht
        }

        $this->assertSame('Origineel', $feedback->fresh()->message);
    }

    public function test_feedback_survives_when_the_lead_is_deleted(): void
    {
        $feedback = LeadFeedback::factory()->create(['lead_name' => 'Jan de Vries']);

        $feedback->lead->delete();

        $feedback->refresh();
        $this->assertNull($feedback->lead_id);
        $this->assertSame('Jan de Vries', $feedback->lead_name);
    }

    public function test_empty_feedback_is_rejected(): void
    {
        Mail::fake();
        $lead = $this->lostLead(daysAgo: 20, attributes: ['feedback_token' => 'geldige-token']);

        $this->post(route('feedback.store', ['token' => 'geldige-token']), ['message' => ''])
            ->assertSessionHasErrors('message');

        $this->assertSame(0, LeadFeedback::count());
        Mail::assertNothingSent();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function lostLead(int $daysAgo, array $attributes = []): Lead
    {
        $lead = Lead::factory()->verloren()->create();
        $lead->forceFill(['lost_at' => now()->subDays($daysAgo), ...$attributes])->save();

        return $lead;
    }
}
