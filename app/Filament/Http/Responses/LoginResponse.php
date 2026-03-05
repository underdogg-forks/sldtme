<?php

declare(strict_types=1);

namespace App\Filament\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Log;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $user = Auth::user();

        Log::info('LoginResponse redirecting authenticated user', [
            'user_id' => $user?->id,
            'email'   => $user?->email,
        ]);

        // Redirect to dashboard
        return redirect()->intended(route('dashboard'));
    }
}
