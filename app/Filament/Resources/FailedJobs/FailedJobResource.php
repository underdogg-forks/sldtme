<?php

namespace App\Filament\Resources\FailedJobs;

use App\Filament\Resources\FailedJobs\Pages\ListFailedJobs;
use App\Filament\Resources\FailedJobs\Schemas\FailedJobForm;
use App\Filament\Resources\FailedJobs\Tables\FailedJobsTable;
use App\Models\FailedJob;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * @source https://gitlab.com/amvisor/filament-failed-jobs
 */
class FailedJobResource extends Resource
{
    protected static ?string $model = FailedJob::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationCircle;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    public static function getNavigationBadge(): ?string
    {
        return (string) FailedJob::query()->count();
    }

    public static function form(Schema $schema): Schema
    {
        return FailedJobForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FailedJobsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFailedJobs::route('/'),
        ];
    }
}
