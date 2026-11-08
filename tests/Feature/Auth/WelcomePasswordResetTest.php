<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Http\Controllers\Auth\NewPasswordController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Features;
use Tests\TestCase;

final class WelcomePasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private const string NEW_PASSWORD = 'Sterk-Wachtwoord-2026!';

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::resetPasswords());
    }

    public function test_the_welcome_broker_is_configured_for_seven_days(): void
    {
        $this->assertSame(10080, config('auth.passwords.welcome.expire'));
        $this->assertSame('welcome_password_tokens', config('auth.passwords.welcome.table'));
    }

    public function test_the_reset_form_accepts_a_welcome_token_after_more_than_an_hour(): void
    {
        $user = User::factory()->create();
        $token = Password::broker(NewPasswordController::WELCOME_BROKER)->createToken($user);

        $this->travel(3)->days();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->fresh()->password));
        $this->assertDatabaseCount('welcome_password_tokens', 0);
    }

    public function test_a_welcome_token_expires_after_seven_days(): void
    {
        $user = User::factory()->create();
        $token = Password::broker(NewPasswordController::WELCOME_BROKER)->createToken($user);

        $this->travel(8)->days();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])->assertSessionHasErrors('email');

        $this->assertFalse(Hash::check(self::NEW_PASSWORD, $user->fresh()->password));
    }

    public function test_a_forgot_password_token_still_expires_after_an_hour(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->travel(2)->hours();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])->assertSessionHasErrors('email');

        $this->assertFalse(Hash::check(self::NEW_PASSWORD, $user->fresh()->password));
    }

    public function test_a_fresh_forgot_password_token_still_works(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->fresh()->password));
    }
}
