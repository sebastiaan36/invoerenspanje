<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Tests\TestCase;

final class CsrfCookieTest extends TestCase
{
    public function test_it_issues_a_fresh_xsrf_cookie_without_content(): void
    {
        $this->get(route('csrf.cookie'))
            ->assertNoContent()
            ->assertCookie('XSRF-TOKEN');
    }
}
