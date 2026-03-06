<?php

namespace App\Filament\Resources\TimeEntries\Tables;

use App\Models\TimeEntry;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TimeEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->searchable()
                    ->label('Description'),
                TextColumn::make('user.email')
                    ->label('User'),
                TextColumn::make('project.name')
                    ->label('Project'),
                TextColumn::make('task.name')
                    ->label('Task'),
                TextColumn::make('time')
                    ->getStateUsing(function (TimeEntry $record): string {
                        return ($record->getDuration()?->cascade()?->forHumans() ?? '-') . ' '
                            . ' (' . $record->start->toDateTimeString('minute') . ' - '
                            . ($record->end?->toDateTimeString('minute') ?? '...') . ')';
                    })
                    ->label('Time'),
                TextColumn::make('organization.name')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('organization')
                    ->label('Organization')
                    ->relationship('organization', 'name')
                    ->searchable(),
                SelectFilter::make('organization_id')
                    ->label('Organization ID')
                    ->relationship('organization', 'id')
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
