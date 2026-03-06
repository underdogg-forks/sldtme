<?php

namespace App\Filament\Resources\OrganizationInvitations;

use App\Filament\Resources\OrganizationInvitations\Pages\ListOrganizationInvitations;
use App\Filament\Resources\OrganizationInvitations\Schemas\OrganizationInvitationForm;
use App\Filament\Resources\OrganizationInvitations\Tables\OrganizationInvitationsTable;
use App\Models\OrganizationInvitation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class OrganizationInvitationResource extends Resource
{
    protected static ?string $model = OrganizationInvitation::class;

    protected static ?string $label = 'Invitations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;

    protected static string|UnitEnum|null $navigationGroup = 'Users';

    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return OrganizationInvitationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrganizationInvitationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrganizationInvitations::route('/'),
        ];
    }
}
