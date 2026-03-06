<?php

namespace App\Filament\Resources\FailedJobs\Tables;

use App\Models\FailedJob;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;

class FailedJobsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')->sortable()->searchable()->toggleable(),
                TextColumn::make('failed_at')->sortable()->searchable(false)->toggleable(),
                TextColumn::make('exception')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->wrap()
                    ->limit(200)
                    ->tooltip(fn (FailedJob $record) => "{$record->failed_at} UUID: {$record->uuid}; Connection: {$record->connection}; Queue: {$record->queue};"),
                TextColumn::make('uuid')->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('connection')->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('queue')->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->toolbarActions([
                \Filament\Actions\BulkAction::make('retry')
                    ->icon('heroicon-o-arrow-path')
                    ->label('Retry selected')
                    ->requiresConfirmation()
                    ->action(function (Collection $records): void {
                        /** @var FailedJob $record */
                        foreach ($records as $record) {
                            Artisan::call("queue:retry {$record->uuid}");
                        }
                        Notification::make()
                            ->title("{$records->count()} jobs have been pushed back onto the queue.")
                            ->success()
                            ->send();
                    }),
                \Filament\Actions\DeleteBulkAction::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    DeleteAction::make(),
                    ViewAction::make(),
                    Action::make('retry')
                        ->icon('heroicon-o-arrow-path')
                        ->label('Retry')
                        ->requiresConfirmation()
                        ->action(function (FailedJob $record): void {
                            Artisan::call("queue:retry {$record->uuid}");
                            Notification::make()
                                ->title("The job with uuid '{$record->uuid}' has been pushed back onto the queue.")
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
