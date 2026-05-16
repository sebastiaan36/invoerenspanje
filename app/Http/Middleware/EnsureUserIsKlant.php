<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserIsKlant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Admins horen niet in het klantportaal — stuur ze naar hun eigen panel.
        if ($user->isAdmin()) {
            return redirect('/admin');
        }

        return $next($request);
    }
}
