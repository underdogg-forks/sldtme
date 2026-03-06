<?php

namespace App\Filament\Resources\Reports\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Novadaemon\FilamentPrettyJson\Form\PrettyJsonField;

class ReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('description')
                    ->label('Description')
                    ->nullable()
                    ->maxLength(255),
                Toggle::make('is_public')
                    ->label('Is public?')
                    ->required(),
                DateTimePicker::make('public_until')
                    ->label('Public until')
                    ->nullable(),
                Select::make('organization_id')
                    ->label('Organization')
                    ->relationship(name: 'organization', titleAttribute: 'name')
                    ->searchable(['name'])
                    ->disabled()
                    ->required(),
                TextInput::make('share_secret')
                    ->label('Share Secret')
                    ->nullable(),
                PrettyJsonField::make('properties')
                    ->formatStateUsing(function ($state, $record): string {
                        if ($state === null || $record === null) {
                            return '';
                        }

                        // If $state is a DTO, get the raw original, otherwise fallback
                        return $record->getRawOriginal('properties') ?? '';
                    })
                    ->disabled(),
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
