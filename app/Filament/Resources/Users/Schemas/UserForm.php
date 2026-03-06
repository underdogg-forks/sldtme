<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Weekday;
use App\Models\User;
use App\Service\TimezoneService;
use Brick\Money\ISOCurrencyProvider;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Korridor\LaravelModelValidationRules\Rules\UniqueEloquent;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        /** @var User|null $record */
        $record = $schema->getRecord();

        return $schema
            ->columns(1)
            ->components([
                TextInput::make('id')
                    ->label('ID')
                    ->disabled()
                    ->visibleOn(['update', 'show'])
                    ->readOnly()
                    ->maxLength(255),
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->required()
                    ->rules($record?->is_placeholder ? [] : [
                        UniqueEloquent::make(User::class, 'email')
                            ->ignore($record?->getKey()),
                    ])
                    ->rule([
                        'email',
                    ])
                    ->maxLength(255),
                Toggle::make('is_placeholder')
                    ->label('Is Placeholder?')
                    ->hiddenOn(['create'])
                    ->disabledOn(['edit']),
                DateTimePicker::make('email_verified_at')
                    ->label('Email Verified At')
                    ->hiddenOn(['create'])
                    ->nullable(),
                Toggle::make('is_email_verified')
                    ->label('Email Verified?')
                    ->visibleOn(['create']),
                Select::make('timezone')
                    ->label('Timezone')
                    ->options(fn (): array => app(TimezoneService::class)->getSelectOptions())
                    ->searchable()
                    ->required(),
                Select::make('week_start')
                    ->label('Week Start')
                    ->options(Weekday::class)
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->label('Password')
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->hiddenOn(['create'])
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255),
                TextInput::make('password_create')
                    ->password()
                    ->label('Password')
                    ->visibleOn(['create'])
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255),
                Select::make('currency')
                    ->label('Currency (Personal Organization)')
                    ->options(function (): array {
                        $currencies = ISOCurrencyProvider::getInstance()->getAvailableCurrencies();
                        $select     = [];
                        foreach ($currencies as $currency) {
                            $currencyCode          = $currency->getCurrencyCode();
                            $select[$currencyCode] = $currency->getName() . ' (' . $currencyCode . ')';
                        }

                        return $select;
                    })
                    ->required()
                    ->visibleOn(['create'])
                    ->searchable(),
                DateTimePicker::make('created_at')
                    ->label('Created At')
                    ->hiddenOn(['create'])
                    ->disabled(),
                DateTimePicker::make('updated_at')
                    ->label('Updated At')
                    ->hiddenOn(['create'])
                    ->disabled(),
            ]);
    }
}
