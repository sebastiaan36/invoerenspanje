<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Http\Responses\NeutralPasswordResetLinkResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;
use Tests\TestCase;

/**
 * The forgot-password form must not reveal which e-mail addresses have an account.
 */
final class PasswordResetPrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::resetPasswords());
    }

    public function test_known_and_unknown_addresses_get_the_same_neutral_status(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $known = $this->from(route('password.request'))->post(route('password.email'), ['email' => $user->email]);
        $unknown = $this->from(route('password.request'))->post(route('password.email'), ['email' => 'niemand@example.com']);

        foreach ([$known, $unknown] as $response) {
            $response->assertRedirect(route('password.request'))
                ->assertSessionHasNoErrors()
                ->assertSessionHas('status', NeutralPasswordResetLinkResponse::MESSAGE);
        }
    }

    public function test_a_repeated_request_for_a_known_address_also_gets_the_neutral_status(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email]);

        // The password broker throttles a second link; that must not show up as an error either.
        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', NeutralPasswordResetLinkResponse::MESSAGE);
    }

    public function test_json_requests_get_the_same_message_for_unknown_addresses(): void
    {
        $this->postJson(route('password.email'), ['email' => 'niemand@example.com'])
            ->assertOk()
            ->assertJson(['message' => NeutralPasswordResetLinkResponse::MESSAGE]);
    }

    public function test_forgot_password_requests_are_rate_limited(): void
    {
        Notification::fake();

        foreach (range(1, 6) as $attempt) {
            $this->post(route('password.email'), ['email' => "persoon{$attempt}@example.com"])->assertRedirect();
        }

        $this->post(route('password.email'), ['email' => 'nog-een@example.com'])->assertTooManyRequests();
    }
}
