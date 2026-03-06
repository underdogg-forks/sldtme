<?php

namespace App\Filament\Resources\Organizations\Tables;

use App\Models\Organization;
use App\Service\DeletionService;
use App\Service\Export\ExportService;
use App\Service\Import\Importers\ImporterProvider;
use App\Service\Import\Importers\ImportException;
use App\Service\Import\Importers\ReportDto;
use App\Service\Import\ImportService;
use App\Service\TimezoneService;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class OrganizationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('personal_team')
                    ->boolean()
                    ->label('Is personal?')
                    ->sortable(),
                TextColumn::make('owner.email')
                    ->sortable(),
                TextColumn::make('currency'),
                TextColumn::make('billable_rate')
                    ->money(fn (Organization $resource) => $resource->currency, divideBy: 100),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->using(function (Organization $record): void {
                            app(DeletionService::class)->deleteOrganization($record);
                        }),
                    Action::make('Export')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Organization $record) {
                            try {
                                $file = app(ExportService::class)->export($record);
                                Notification::make()
                                    ->title('Export successful')
                                    ->success()
                                    ->persistent()
                                    ->send();

                                return response()->streamDownload(function () use ($file): void {
                                    echo Storage::disk(config('filesystems.private'))->get($file);
                                }, 'export.zip');
                            } catch (Exception $exception) {
                                report($exception);
                                Notification::make()
                                    ->title('Export failed')
                                    ->danger()
                                    ->body('Message: ' . $exception->getMessage())
                                    ->persistent()
                                    ->send();
                            }
                        }),
                    Action::make('Import')
                        ->icon('heroicon-o-inbox-arrow-down')
                        ->action(function (Organization $record, array $data): void {
                            try {
                                $file = Storage::disk(config('filament.default_filesystem_disk'))->get($data['file']);
                                if ($file === null) {
                                    throw new Exception('File not found');
                                }
                                /** @var string $timezone */
                                $timezone = $data['timezone'];
                                /** @var ReportDto $report */
                                $report = app(ImportService::class)->import(
                                    $record,
                                    $data['type'],
                                    $file,
                                    $timezone
                                );
                                Notification::make()
                                    ->title('Import successful')
                                    ->success()
                                    ->body(
                                        'Imported time entries: ' . $report->timeEntriesCreated . '<br>'
                                        . 'Imported clients: ' . $report->clientsCreated . '<br>'
                                        . 'Imported projects: ' . $report->projectsCreated . '<br>'
                                        . 'Imported tasks: ' . $report->tasksCreated . '<br>'
                                        . 'Imported tags: ' . $report->tagsCreated . '<br>'
                                        . 'Imported users: ' . $report->usersCreated
                                    )
                                    ->persistent()
                                    ->send();
                            } catch (ImportException $exception) {
                                report($exception);
                                Notification::make()
                                    ->title('Import failed, changes rolled back')
                                    ->danger()
                                    ->body('Message: ' . $exception->getMessage())
                                    ->persistent()
                                    ->send();
                            }
                        })
                        ->tooltip(fn (Organization $record): string => 'Import into ' . $record->name)
                        ->schema([
                            FileUpload::make('file')
                                ->label('File')
                                ->required(),
                            Select::make('type')
                                ->required()
                                ->options(function (): array {
                                    $select = [];
                                    foreach (app(ImporterProvider::class)->getImporterKeys() as $key) {
                                        $select[$key] = $key;
                                    }
                                    return $select;
                                }),
                            Select::make('timezone')
                                ->label('Timezone')
                                ->options(fn (): array => app(TimezoneService::class)->getSelectOptions())
                                ->searchable()
                                ->required(),
                        ]),
                ]),
            ])
            ->toolbarActions([
            ]);
    }
}
