<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Filament\Resources\ProjectMembers\ProjectMemberResource;
use App\Models\ProjectMember;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectMembersRelationManager extends RelationManager
{
    protected static ?string $title = 'Project Members';

    protected static string $relationship = 'members';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('user.name'),
                TextColumn::make('billable_rate')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
            ])
            ->headerActions([
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view')
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->url(fn (ProjectMember $record): string => ProjectMemberResource::getUrl('view', [
                            'record' => $record->getKey(),
                        ])),
                    Action::make('edit')
                        ->icon('heroicon-o-pencil')
                        ->url(fn (ProjectMember $record): string => ProjectMemberResource::getUrl('edit', [
                            'record' => $record->getKey(),
                        ]))
                        ->openUrlInNewTab(),
                ]),
            ])
            ->toolbarActions([
            ]);
    }
}
