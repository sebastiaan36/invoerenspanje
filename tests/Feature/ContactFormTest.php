<?php

namespace Tests\Feature;

use App\Mail\ContactFormNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The contact page submits through Inertia's form.post(), which needs a
     * redirect (not JSON) for its success state to show.
     */
    public function test_valid_submission_queues_mail_and_redirects_back_with_flash(): void
    {
        Mail::fake();

        $this->from(route('contact'))
            ->post(route('contact.store'), [
                'name' => 'Jan Jansen',
                'email' => 'jan@example.com',
                'message' => 'Mijn auto moet naar Spanje.',
            ])
            ->assertRedirect(route('contact'))
            ->assertSessionHas('success');

        Mail::assertQueued(ContactFormNotification::class, fn (ContactFormNotification $mail) => $mail->contactMessage === 'Mijn auto moet naar Spanje.'
            && str_contains($mail->render(), 'Mijn auto moet naar Spanje.'));
    }

    public function test_mail_failure_is_logged_and_the_visitor_still_sees_success(): void
    {
        Mail::shouldReceive('to')->andThrow(new RuntimeException('Queue down'));
        Log::spy();

        $this->from(route('contact'))
            ->post(route('contact.store'), [
                'name' => 'Jan Jansen',
                'email' => 'jan@example.com',
                'message' => 'Mijn auto moet naar Spanje.',
            ])
            ->assertRedirect(route('contact'))
            ->assertSessionHas('success');

        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => $context['contact']['message'] === 'Mijn auto moet naar Spanje.'
                && $context['exception'] === 'Queue down');
    }

    public function test_invalid_submission_returns_validation_errors(): void
    {
        Mail::fake();

        $this->from(route('contact'))
            ->post(route('contact.store'), ['name' => '', 'email' => 'geen-email', 'message' => ''])
            ->assertRedirect(route('contact'))
            ->assertSessionHasErrors(['name', 'email', 'message']);

        Mail::assertNothingQueued();
    }

    public function test_tarieven_page_shares_the_spanish_fixed_costs(): void
    {
        $this->get(route('tarieven'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('fixedCosts', count(config('spain_import.fixed_costs')))
                ->where('fixedCosts.2.key', 'honorarios')
                ->where('fixedCosts.2.amount_eur', 219.62)
            );
    }
}
