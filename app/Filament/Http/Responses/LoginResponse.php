<?php

declare(strict_types=1);

namespace App\Filament\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportRedirects\Redirector;
use Log;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = Auth::user();

        Log::info('LoginResponse redirecting authenticated user', [
            'user_id' => $user?->id,
            'email'   => $user?->email,
        ]);

        // Redirect to admin dashboard
        return redirect()->intended(route('filament.admin.pages.dashboard'));
    }
}
