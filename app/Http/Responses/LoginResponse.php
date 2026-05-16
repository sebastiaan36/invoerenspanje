<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

final class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 204);
        }

        /** @var User|null $user */
        $user = $request->user();

        $target = $user?->isAdmin() ? '/admin' : '/portaal';

        return redirect()->intended($target);
    }
}
