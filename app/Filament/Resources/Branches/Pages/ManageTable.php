<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Models\Table as TableModel;

use App\Filament\Resources\Branches\BranchResource;
use App\Filament\Resources\Branches\Resources\Tables\TableResource;

use Filament\Tables\Table;
use Filament\Actions\Action;

use Filament\Resources\Pages\ManageRelatedRecords;

class ManageTable extends ManageRelatedRecords
{
    protected static string $resource = BranchResource::class;

    protected static string $relationship = 'tables';

    protected static ?string $relatedResource = TableResource::class;

    public function getTitle(): string
    {
        return __('Quản lý bàn chi nhánh :name', ['name' => $this->getRecord()->name]);
    }

    public function getBreadcrumbs(): array
    {
        return [
            BranchResource::getIndexUrl() => __('Chi nhánh'),
            $this->getRecord()->name,
            __('Quản lý bàn'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label(__('Tạo Bàn'))
                ->action(function (): void {
                    $branchId = $this->getRecord()->id;

                    TableModel::create([
                        'branch_id' => $branchId,
                        'table_number' => TableModel::generateTableNumber($branchId),
                    ]);
                })
                ->requiresConfirmation()
                ->successNotificationTitle(__('Tạo bàn thành công')),
        ];
    }

    public function table(Table $table): Table
    {
        return $table;
    }
}
