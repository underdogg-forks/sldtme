<?php

namespace App\Filament\Pages\Auth;

use App\Filament\Http\Responses\LoginResponse;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Log;

class Login extends \Filament\Auth\Pages\Login
{
    public function authenticate(): \Filament\Auth\Http\Responses\Contracts\LoginResponse
    {
        /*try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }*/

        $data = $this->form->getState();

        // Debug: Log form data
        Log::info('Filament login attempt', [
            'email'        => $data['email'] ?? 'missing',
            'has_password' => isset($data['password']),
        ]);

        // Get credentials from form
        $credentials = $this->getCredentialsFromFormState($data);

        Log::info('Attempting authentication with credentials', [
            'email'        => $credentials['email'] ?? 'missing',
            'has_password' => isset($credentials['password']),
        ]);

        // Attempt authentication
        $authenticated = Filament::auth()->attempt($credentials, $data['remember'] ?? false);

        /*Log::info('Authentication result', [
            'authenticated' => $authenticated,
            'guard'         => Filament::auth()->guard(),
        ]);*/

        if ( ! $authenticated) {
            Log::warning('Authentication failed for email', [
                'email' => $credentials['email'] ?? 'missing',
            ]);
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        Log::info('User authenticated successfully', [
            'user_id'    => $user?->id,
            'user_email' => $user?->email,
        ]);

        if (
            ($user instanceof FilamentUser)
            && ( ! $user->canAccessPanel(Filament::getCurrentOrDefaultPanel()))
        ) {
            Log::warning('User cannot access panel', [
                'user_id' => $user->id,
                'panel'   => Filament::getCurrentOrDefaultPanel()->getId(),
            ]);

            Filament::auth()->logout();

            $this->throwFailureValidationException();
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function getCredentialsFromFormState(array $state): array
    {
        return [
            'email'    => $state['email'],
            'password' => $state['password'],
        ];
    }

    protected function getAuthenticatedUser(array $credentials): ?object
    {
        Log::info('getAuthenticatedUser called', [
            'email' => $credentials['email'] ?? 'missing',
        ]);

        // Use the same authentication logic as Fortify
        $user = $this->getUserModel()
            ->where('email', $credentials['email'])
            ->where('is_placeholder', '=', false)
            ->first();

        Log::info('User lookup result', [
            'email'               => $credentials['email'],
            'user_found'          => $user !== null,
            'user_id'             => $user?->id,
            'user_is_placeholder' => $user?->is_placeholder,
        ]);

        if ($user === null) {
            Log::warning('User not found or is placeholder', [
                'email' => $credentials['email'],
            ]);

            return null;
        }

        $passwordMatches = Hash::check($credentials['password'], $user->password);

        Log::info('Password check result', [
            'email'                    => $credentials['email'],
            'password_matches'         => $passwordMatches,
            'stored_password_length'   => mb_strlen($user->password),
            'provided_password_length' => mb_strlen($credentials['password'] ?? ''),
        ]);

        if ( ! $passwordMatches) {
            Log::warning('Password mismatch for user', [
                'email' => $credentials['email'],
            ]);

            return null;
        }

        Log::info('User authenticated successfully', [
            'email'   => $credentials['email'],
            'user_id' => $user->id,
        ]);

        return $user;
    }

    protected function throwFailureValidationException(): never
    {
        Log::error('Login validation exception thrown - authentication failed', [
            'ip'         => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        throw ValidationException::withMessages([
            'data.email' => __('filament-panels::pages/auth/login.messages.invalid_credentials'),
        ]);
    }

    protected function getEmailFormComponent(): \Filament\Schemas\Components\Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::pages/auth/login.form.email.label'))
            ->email()
            ->required()
            ->autocomplete('email')
            ->autofocus();
    }

    protected function getPasswordFormComponent(): \Filament\Schemas\Components\Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::pages/auth/login.form.password.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->autocomplete('current-password');
    }

    protected function getRememberMeFormComponent(): \Filament\Schemas\Components\Component
    {
        return Checkbox::make('remember')
            ->label(__('filament-panels::pages/auth/login.form.remember.label'));
    }

    protected function getLoginFormComponent(): \Filament\Schemas\Components\Component
    {
        return parent::getLoginFormComponent()
            ->action($this->getSubmitFormAction());
    }
}
