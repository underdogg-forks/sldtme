<?php

namespace App\Filament\Resources\ProjectMembers;

use App\Filament\Resources\ProjectMembers\Pages\ListProjectMembers;
use App\Filament\Resources\ProjectMembers\Schemas\ProjectMemberForm;
use App\Filament\Resources\ProjectMembers\Tables\ProjectMembersTable;
use App\Models\ProjectMember;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProjectMemberResource extends Resource
{
    protected static ?string $model = ProjectMember::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return ProjectMemberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectMembersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjectMembers::route('/'),
        ];
    }
}
