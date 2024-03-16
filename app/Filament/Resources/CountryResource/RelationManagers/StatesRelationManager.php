<?php

namespace App\Filament\Resources\CountryResource\RelationManagers;

use Filament\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Notification;

class StatesRelationManager extends RelationManager
{
    protected static string $relationship = 'states';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Main Info')
                    ->description('Put the main information details in.')
                    ->schema([
                        Forms\Components\Select::make('country_id')
                            // ->options([
                            //     'active'    => 'Active',
                            //     'inactive'  => 'Inactive',
                            // ])
                            ->relationship(name: 'country', titleAttribute: 'name')
                            ->searchable()
                            ->native(false)
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2)
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('country.name')
                    ->label('Country')
                    ->numeric()
                    ->searchable(isIndividual: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('State Name')
                    ->searchable(isGlobal: true)
                    // ->hidden(!Auth::user()->email === 'admin@example.com')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('country.name')
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
