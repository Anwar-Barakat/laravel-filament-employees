<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\Employee;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'All'       => Tab::make(),
            'This Week' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->where('date_hire', '>=', now()->subWeek()))
                ->badge(Employee::where('date_hire', '>=', now()->subWeek())->count()),
            'This Month' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->where('date_hire', '>=', now()->subMonth()))
                ->badge(Employee::where('date_hire', '>=', now()->subMonth())->count()),
            'This Year' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->where('date_hire', '>=', now()->subYear()))
                ->badge(Employee::where('date_hire', '>=', now()->subYear())->count()),
        ];
    }
}