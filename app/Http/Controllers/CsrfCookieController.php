<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * Empty response whose only job is the fresh XSRF-TOKEN cookie the web
 * middleware attaches. The homepage calculator calls it after a 419
 * (expired session) and then retries its request once.
 */
final class CsrfCookieController extends Controller
{
    public function __invoke(): Response
    {
        return response()->noContent();
    }
}
