<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\City;
use App\Models\Employee;
use App\Models\State;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Filament\Notifications\Notification;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Employees';

    protected static ?string $modelLabel = 'Employees';

    protected static ?string $navigationGroup = 'Employee Management';

    protected static ?string $slug = 'employees';

    protected static ?string $recordTitleAttribute = 'first_name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Name')
                    ->description('Put the user name details in.')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('middle_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                    ])->columns(3),
                Forms\Components\Section::make('User Address')
                    ->description('Put the user address details in.')
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('zip_code')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),
                Forms\Components\Section::make('Dates')
                    ->schema([
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(),
                        Forms\Components\DatePicker::make('date_hire')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(),
                    ])->columns(2),
                Forms\Components\Section::make('Relationships')
                    ->schema([
                        Forms\Components\Select::make('country_id')
                            ->relationship(name: 'country', titleAttribute: 'name')
                            ->searchable()
                            ->native(false)
                            ->preload()
                            ->afterStateUpdated(function (Set $set) {
                                $set('state_id', null);
                                $set('city_id', null);
                            })
                            ->live()
                            ->required(),
                        Forms\Components\Select::make('state_id')
                            ->options(fn (Get $get): Collection => State::query()->where('country_id', $get('country_id'))->pluck('name', 'id'))
                            ->searchable()
                            ->native(false)
                            ->afterStateUpdated(fn (Set $set) => $set('city_id', null))
                            ->preload()
                            ->live()
                            ->required(),
                        Forms\Components\Select::make('city_id')
                            ->options(fn (Get $get): Collection => City::query()->where('state_id', $get('state_id'))->pluck('name', 'id'))
                            ->searchable()
                            ->native(false)
                            ->preload()
                            ->live()
                            ->required(),
                        Forms\Components\Select::make('department_id')
                            ->relationship(name: 'department', titleAttribute: 'name')
                            ->searchable()
                            ->native(false)
                            ->preload()
                            ->required(),
                    ])->columns(2),




            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('country.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('state.name')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('city.name')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('department.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('middle_name')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('zip_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('date_hire')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters(
                [
                    SelectFilter::make('Department')
                        ->relationship('department', 'name')
                        ->searchable()
                        ->preload()
                        ->label('Filter by Department')
                        ->indicator('Department'),
                    Filter::make('created_at')
                        ->form([
                            DatePicker::make('created_from'),
                            DatePicker::make('created_until'),
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            return $query
                                ->when(
                                    $data['created_from'],
                                    fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                                )
                                ->when(
                                    $data['created_until'],
                                    fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                                );
                        })
                    // ->columnSpan(2)->columns(2)
                ],
                // layout: FiltersLayout::AboveContent
            )
            // ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->successNotification(
                        Notification::make()->danger()->title('Employee Deleted')
                            ->body('Employee Has Deleted successfully.')
                    )
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->first_name . ' ' . $record->last_name;
    }
    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'last_name', 'middle_name', 'country.name'];
    }

    // Anwar Barakat
    // Country: Syria
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Country' => $record->country->name,
        ];
    }

    // to apply eager loading
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['country']);
    }

    // badge on the navbar
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 2 ? 'info' : 'warning';
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'view' => Pages\ViewEmployee::route('/{record}'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}