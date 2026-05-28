<?php

namespace App\Filament\Resources\Branches\Tables;

use App\Filament\Resources\Branches\BranchResource;
use App\Models\Branch;
use App\Models\Kitchen;
use App\Models\Table as TableModel;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;

use Filament\Actions\BulkActionGroup;

use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;

use Illuminate\Support\Facades\DB;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Tên chi nhánh'))
                    ->searchable(),
                TextColumn::make('deleted_at')
                    ->label(__('Đã xóa lúc'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Tạo lúc'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Cập nhật lúc'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->default('trashed'),
            ])
            ->recordActions([
                Action::make('manage-table')
                    ->label(__('Quản lý bàn'))
                    ->url(fn($record): string => BranchResource::getUrl('table-management', ['record' => $record->id]))
                    ->visible(fn($record): bool => $record->deleted_at === null),
                Action::make('manage-kitchen')
                    ->label(__('Quản lý nhà bếp'))
                    ->url(fn ($record): string => BranchResource::getUrl('kitchen-management', ['record' => $record->id]))
                    ->visible(fn ($record): bool => $record->deleted_at === null),
                Action::make('copy-branch')
                    ->label(__('Sao chép'))
                    ->icon(Heroicon::OutlinedDocumentDuplicate)
                    ->color('info')
                    ->visible(fn ($record): bool => $record->deleted_at === null)
                    ->modalHeading(fn ($record): string => __('Sao chép chi nhánh: :name', ['name' => $record->name]))
                    ->modalSubmitActionLabel(__('Sao chép'))
                    ->form([
                        Radio::make('copy_mode')
                            ->label(__('Chế độ sao chép'))
                            ->options([
                                'new' => __('Tạo chi nhánh mới'),
                                'existing' => __('Ghi đè vào chi nhánh có sẵn'),
                            ])
                            ->default('new')
                            ->required()
                            ->live(),
                        TextInput::make('new_branch_name')
                            ->label(__('Tên chi nhánh mới'))
                            ->required(fn (Get $get): bool => $get('copy_mode') === 'new')
                            ->visible(fn (Get $get): bool => $get('copy_mode') === 'new')
                            ->maxLength(255),
                        Select::make('target_branch_id')
                            ->label(__('Chi nhánh đích'))
                            ->options(fn () => Branch::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(fn (Get $get): bool => $get('copy_mode') === 'existing')
                            ->visible(fn (Get $get): bool => $get('copy_mode') === 'existing'),
                        Radio::make('existing_copy_strategy')
                            ->label(__('Cách sao chép'))
                            ->options([
                                'overwrite' => __('Ghi đè (xóa dữ liệu cũ, thay bằng mới)'),
                                'merge' => __('Thêm vào (giữ dữ liệu cũ, thêm dữ liệu mới)'),
                            ])
                            ->default('overwrite')
                            ->required(fn (Get $get): bool => $get('copy_mode') === 'existing')
                            ->visible(fn (Get $get): bool => $get('copy_mode') === 'existing'),
                        Toggle::make('copy_kitchens')
                            ->label(__('Sao chép bếp'))
                            ->default(true),
                        Toggle::make('copy_tables')
                            ->label(__('Sao chép bàn'))
                            ->default(true),
                    ])
                    ->action(function (Branch $record, array $data): void {
                        if (
                            $data['copy_mode'] === 'existing'
                            && (int) $data['target_branch_id'] === $record->id
                        ) {
                            Notification::make()
                                ->title(__('Không thể sao chép vào chính chi nhánh này'))
                                ->danger()
                                ->send();

                            return;
                        }

                        DB::transaction(function () use ($record, $data): void {
                            if ($data['copy_mode'] === 'new') {
                                $targetBranch = Branch::create(['name' => $data['new_branch_name']]);
                            } else {
                                $targetBranch = Branch::findOrFail((int) $data['target_branch_id']);
                                $strategy = $data['existing_copy_strategy'] ?? 'overwrite';

                                if ($strategy === 'overwrite') {
                                    if ($data['copy_kitchens']) {
                                        Kitchen::withTrashed()->where('branch_id', $targetBranch->id)->forceDelete();
                                    }

                                    if ($data['copy_tables']) {
                                        TableModel::withTrashed()->where('branch_id', $targetBranch->id)->forceDelete();
                                    }
                                }
                            }

                            if ($data['copy_kitchens']) {
                                $kitchens = $record->kitchens()->with('cookingMethods')->get();

                                foreach ($kitchens as $kitchen) {
                                    $newKitchen = Kitchen::create([
                                        'name' => $kitchen->name,
                                        'branch_id' => $targetBranch->id,
                                    ]);

                                    if ($kitchen->cookingMethods->isNotEmpty()) {
                                        $newKitchen->cookingMethods()->attach(
                                            $kitchen->cookingMethods->pluck('id')
                                        );
                                    }
                                }
                            }

                            if ($data['copy_tables']) {
                                $tables = $record->tables()->get();
                                $isMerge = ($data['copy_mode'] === 'existing')
                                    && ($data['existing_copy_strategy'] ?? 'overwrite') === 'merge';

                                foreach ($tables as $tableRecord) {
                                    TableModel::create([
                                        'branch_id' => $targetBranch->id,
                                        'table_number' => $isMerge
                                            ? TableModel::generateTableNumber($targetBranch->id)
                                            : $tableRecord->table_number,
                                        'is_active' => $tableRecord->is_active,
                                    ]);
                                }
                            }
                        });

                        Notification::make()
                            ->title(__('Sao chép chi nhánh thành công'))
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
