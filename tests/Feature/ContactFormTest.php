<?php

namespace Tests\Feature;

use App\Mail\ContactFormNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The contact page submits through Inertia's form.post(), which needs a
     * redirect (not JSON) for its success state to show.
     */
    public function test_valid_submission_sends_mail_and_redirects_back_with_flash(): void
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

        Mail::assertSent(ContactFormNotification::class, fn (ContactFormNotification $mail) => $mail->contactMessage === 'Mijn auto moet naar Spanje.'
            && str_contains($mail->render(), 'Mijn auto moet naar Spanje.'));
    }

    public function test_invalid_submission_returns_validation_errors(): void
    {
        Mail::fake();

        $this->from(route('contact'))
            ->post(route('contact.store'), ['name' => '', 'email' => 'geen-email', 'message' => ''])
            ->assertRedirect(route('contact'))
            ->assertSessionHasErrors(['name', 'email', 'message']);

        Mail::assertNothingSent();
    }

    public function test_tarieven_page_shares_the_spanish_fixed_costs(): void
    {
        $this->get(route('tarieven'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('fixedCosts', count(config('spain_import.fixed_costs')))
                ->where('fixedCosts.3.key', 'honorarios')
            );
    }
}
