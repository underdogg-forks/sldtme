<?php

namespace App\Filament\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements \Filament\Auth\Http\Responses\Contracts\LoginResponse
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
