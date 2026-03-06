<?php

namespace App\Filament\Resources\Audits\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Novadaemon\FilamentPrettyJson\Form\PrettyJsonField;

class AuditForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_type')
                    ->maxLength(255),
                TextInput::make('user_id'),
                TextInput::make('event')
                    ->required()
                    ->maxLength(255),
                TextInput::make('auditable_type')
                    ->required()
                    ->maxLength(255),
                TextInput::make('auditable_id')
                    ->required(),
                PrettyJsonField::make('old_values'),
                PrettyJsonField::make('new_values'),
                Textarea::make('url'),
                TextInput::make('ip_address'),
                TextInput::make('user_agent')
                    ->maxLength(1023),
                TextInput::make('tags')
                    ->maxLength(255),
            ]);
    }
}
