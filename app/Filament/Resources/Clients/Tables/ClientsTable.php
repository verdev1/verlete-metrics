<?php

namespace App\Filament\Resources\Clients\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_test')
                    ->label('Test')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn ($record): ?string => $record->name),
                TextColumn::make('website')
                    ->searchable()
                    ->sortable()
                    ->limit(20)
                    ->tooltip(fn ($record): ?string => $record->website)
                    ->toggleable(),
                TextColumn::make('store')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('emails')
                    ->searchable()
                    ->limit(20)
                    ->tooltip(fn ($record): ?string => $record->emails)
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
