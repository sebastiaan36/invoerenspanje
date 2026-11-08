<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\LeadFeedbackController;
use App\Mail\LeadFeedbackReceivedNotification;
use App\Mail\LostLeadFeedbackMail;
use App\Models\Lead;
use App\Models\LeadFeedback;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Testing\Fakes\MailFake;
use Inertia\Testing\AssertableInertia as Assert;
use RuntimeException;
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
        $lead->ensureFeedbackToken();

        $mail = new LostLeadFeedbackMail($lead, $lead->feedbackUrl());

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
        $this->askedLead(['name' => 'Jan de Vries']);

        $this->get(route('feedback.show', ['token' => 'geldige-token']))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertSee('<title>'.e(config('seo.routes')['feedback.show']['title']).'</title>', false)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Feedback')
                ->where('token', 'geldige-token')
                ->where('firstName', 'Jan')
                ->where('submitted', false)
                ->where('linkExpired', false));
    }

    public function test_unknown_token_returns_404(): void
    {
        $this->get(route('feedback.show', ['token' => 'bestaat-niet']))->assertNotFound();
        $this->post(route('feedback.store', ['token' => 'bestaat-niet']), ['message' => 'Hoi'])->assertNotFound();
    }

    public function test_submitting_feedback_stores_it_and_notifies_us(): void
    {
        Mail::fake();
        $lead = $this->askedLead(['name' => 'Jan de Vries', 'kenteken' => 'XR-778-S']);
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
        $this->askedLead();

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
        $this->askedLead();

        $this->post(route('feedback.store', ['token' => 'geldige-token']), ['message' => ''])
            ->assertSessionHasErrors('message');

        $this->assertSame(0, LeadFeedback::count());
        Mail::assertNothingSent();
    }

    public function test_a_lead_can_send_at_most_three_messages_and_earlier_ones_are_kept(): void
    {
        Mail::fake();
        $this->askedLead();

        foreach (['Eerste', 'Tweede', 'Derde'] as $message) {
            $this->post(route('feedback.store', ['token' => 'geldige-token']), ['message' => $message])
                ->assertSessionHasNoErrors();
        }

        $this->post(route('feedback.store', ['token' => 'geldige-token']), ['message' => 'Vierde'])
            ->assertSessionHasErrors(['message' => LeadFeedbackController::LIMIT_MESSAGE]);

        $this->assertSame(['Eerste', 'Tweede', 'Derde'], LeadFeedback::orderBy('id')->pluck('message')->all());
        Mail::assertSent(LeadFeedbackReceivedNotification::class, 3);
    }

    public function test_link_is_inactive_once_the_lead_is_no_longer_lost(): void
    {
        Mail::fake();
        $lead = $this->askedLead();
        $lead->update(['status' => 'gecontacteerd']);

        $this->get(route('feedback.show', ['token' => 'geldige-token']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Feedback')->where('linkExpired', true));

        $this->post(route('feedback.store', ['token' => 'geldige-token']), ['message' => 'Toch nog iets'])
            ->assertSessionHasErrors(['message' => LeadFeedbackController::EXPIRED_MESSAGE]);

        $this->assertSame(0, LeadFeedback::count());
        Mail::assertNothingSent();
    }

    public function test_link_is_inactive_ninety_days_after_the_mail(): void
    {
        Mail::fake();
        $this->askedLead(['feedback_requested_at' => now()->subDays(89)]);

        $this->get(route('feedback.show', ['token' => 'geldige-token']))
            ->assertInertia(fn (Assert $page) => $page->where('linkExpired', false));

        $this->travel(2)->days();

        $this->get(route('feedback.show', ['token' => 'geldige-token']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('linkExpired', true));
        $this->post(route('feedback.store', ['token' => 'geldige-token']), ['message' => 'Te laat'])
            ->assertSessionHasErrors('message');
        $this->assertSame(0, LeadFeedback::count());
    }

    public function test_link_is_inactive_when_the_mail_was_never_sent(): void
    {
        $this->lostLead(daysAgo: 20, attributes: ['feedback_token' => 'geldige-token']);

        $this->get(route('feedback.show', ['token' => 'geldige-token']))
            ->assertInertia(fn (Assert $page) => $page->where('linkExpired', true));
    }

    public function test_a_failed_mail_leaves_the_lead_unmarked_and_other_leads_still_get_theirs(): void
    {
        $mailer = $this->failingMailFakeFor('kapot@example.com');
        $failing = $this->lostLead(daysAgo: 20, attributes: ['email' => 'kapot@example.com']);
        $healthy = $this->lostLead(daysAgo: 20, attributes: ['email' => 'goed@example.com']);

        $this->artisan('leads:request-feedback')->assertSuccessful();

        $mailer->assertSent(LostLeadFeedbackMail::class, 1);
        $mailer->assertSent(LostLeadFeedbackMail::class, fn (LostLeadFeedbackMail $mail) => $mail->hasTo('goed@example.com'));
        $this->assertNull($failing->fresh()->feedback_requested_at);
        $this->assertSame(1, $failing->fresh()->feedback_request_failures);
        $this->assertNotNull($healthy->fresh()->feedback_requested_at);
        $this->assertSame(0, $healthy->fresh()->feedback_request_failures);
    }

    public function test_command_stops_retrying_a_lead_after_three_failures(): void
    {
        $this->failingMailFakeFor('kapot@example.com');
        $lead = $this->lostLead(daysAgo: 20, attributes: ['email' => 'kapot@example.com']);

        foreach (range(1, 5) as $run) {
            $this->artisan('leads:request-feedback')->assertSuccessful();
        }

        $this->assertSame(Lead::MAX_FEEDBACK_REQUEST_FAILURES, $lead->fresh()->feedback_request_failures);
        $this->assertFalse(Lead::query()->dueForFeedbackRequest()->whereKey($lead->id)->exists());
    }

    public function test_a_lead_lost_again_after_being_reopened_gets_no_second_mail(): void
    {
        Mail::fake();
        $lead = $this->lostLead(daysAgo: 20);
        $this->artisan('leads:request-feedback')->assertSuccessful();

        $lead->refresh()->update(['status' => 'gecontacteerd']);
        $lead->update(['status' => 'verloren']);
        $this->travel(Lead::FEEDBACK_DELAY_DAYS + 1)->days();
        $this->artisan('leads:request-feedback')->assertSuccessful();

        Mail::assertSent(LostLeadFeedbackMail::class, 1);
    }

    public function test_feedback_url_has_no_side_effects(): void
    {
        $lead = $this->lostLead(daysAgo: 20);

        try {
            $lead->feedbackUrl();
            $this->fail('feedbackUrl() zonder token hoort te falen.');
        } catch (\LogicException) {
            // verwacht
        }

        $this->assertNull($lead->fresh()->feedback_token);
    }

    public function test_feedback_submissions_are_rate_limited_per_link(): void
    {
        Mail::fake();
        $this->askedLead();
        $url = route('feedback.store', ['token' => 'geldige-token']);

        foreach (range(1, 5) as $attempt) {
            $this->post($url, ['message' => ''])->assertSessionHasErrors('message');
        }

        $this->post($url, ['message' => ''])->assertTooManyRequests();
        // Eigen teller: andere links en andere formulieren hebben er geen last van.
        $this->post(route('feedback.store', ['token' => 'andere-token']), ['message' => ''])->assertSessionHasErrors('message');
        $this->post(route('contact.store'), [])->assertSessionHasErrors();
    }

    public function test_feedback_page_views_are_rate_limited(): void
    {
        $this->askedLead();

        foreach (range(1, 30) as $attempt) {
            $this->get(route('feedback.show', ['token' => 'geldige-token']))->assertOk();
        }

        $this->get(route('feedback.show', ['token' => 'geldige-token']))->assertTooManyRequests();
    }

    public function test_analytics_and_social_tags_never_see_the_token(): void
    {
        $this->askedLead();
        $this->app->detectEnvironment(fn (): string => 'production');

        $html = $this->get(route('feedback.show', ['token' => 'geldige-token']))->assertOk()->getContent();
        $head = substr($html, 0, strpos($html, '</head>'));

        $this->assertStringContainsString("gtag('config', 'G-MER8N0VEN2', {", $head);
        $this->assertStringContainsString('"page_path":"\/feedback"', $head);
        $this->assertStringNotContainsString('geldige-token', $head);
    }

    public function test_other_pages_keep_the_plain_analytics_config(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');

        $this->get(route('tarieven'))->assertSee("gtag('config', 'G-MER8N0VEN2');", false);
    }

    public function test_the_command_is_scheduled_daily_without_overlapping(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn (Event $event): bool => str_contains((string) $event->command, 'leads:request-feedback'));

        $this->assertNotNull($event, 'leads:request-feedback staat niet in de scheduler.');
        $this->assertSame('0 10 * * *', $event->expression);
        $this->assertSame('Europe/Madrid', $event->timezone);
        $this->assertTrue($event->withoutOverlapping);
        $this->assertSame(30, $event->expiresAt);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function askedLead(array $attributes = []): Lead
    {
        return $this->lostLead(daysAgo: 20, attributes: [
            'feedback_token' => 'geldige-token',
            'feedback_requested_at' => now()->subDays(5),
            ...$attributes,
        ]);
    }

    /**
     * Mail-fake die faalt voor één ontvanger, zodat we het gedrag bij een mislukte verzending kunnen testen.
     */
    private function failingMailFakeFor(string $failingAddress): MailFake
    {
        $fake = new class(app('mail.manager'), $failingAddress) extends MailFake
        {
            public function __construct(MailManager $manager, private readonly string $failingAddress)
            {
                parent::__construct($manager);
            }

            public function send($view, array $data = [], $callback = null)
            {
                if ($view instanceof Mailable && $view->hasTo($this->failingAddress)) {
                    throw new RuntimeException('SMTP onbereikbaar');
                }

                return parent::send($view, $data, $callback);
            }
        };

        Mail::swap($fake);

        return $fake;
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
