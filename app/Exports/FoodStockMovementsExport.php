<?php

namespace App\Exports;

use App\Models\FoodStockMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FoodStockMovementsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private readonly int $branchId,
        private readonly ?string $search = null,
        private readonly ?string $type = null,
    ) {}

    /**
     * @return Collection<int, FoodStockMovement>
     */
    public function collection(): Collection
    {
        return FoodStockMovement::query()
            ->with([
                'stock.food:id,name',
                'createdBy:id,username',
            ])
            ->whereHas('stock', fn (Builder $query): Builder => $query->where('branch_id', $this->branchId))
            ->when($this->search, fn (Builder $query): Builder => $query->whereHas(
                'stock.food',
                fn (Builder $foodQuery): Builder => $foodQuery->where('name', 'like', "%{$this->search}%")
            ))
            ->when($this->type, fn (Builder $query): Builder => $query->where('type', $this->type))
            ->latest('occurred_at')
            ->latest('id')
            ->get();
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Thời gian',
            'Loại ốc',
            'Loại biến động',
            'Số lượng thay đổi',
            'Đơn vị',
            'Tồn trước',
            'Tồn sau',
            'Đơn giá (VND)',
            'Thành tiền (VND)',
            'Người thực hiện',
            'Ghi chú',
        ];
    }

    /**
     * @return array<int, float|int|string|null>
     */
    public function map(mixed $row): array
    {
        /** @var FoodStockMovement $movement */
        $movement = $row;

        return [
            $movement->occurred_at?->format('d/m/Y H:i:s'),
            $movement->stock->food?->name ?? __('Món đã xóa'),
            $this->typeLabel($movement->type),
            (float) $movement->balance_after - (float) $movement->balance_before,
            $this->unitLabel($movement),
            (float) $movement->balance_before,
            (float) $movement->balance_after,
            $movement->unit_price,
            $movement->total_cost,
            $movement->createdBy?->username
                ?? ($movement->bill_detail_id ? "Đơn món #{$movement->bill_detail_id}" : 'Hệ thống'),
            $movement->note,
        ];
    }

    /**
     * @return array<int, array<string, array<string, bool|string>>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['argb' => 'FFB40718'],
                ],
            ],
        ];
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            FoodStockMovement::TYPE_RESTOCK => 'Nhập kho',
            FoodStockMovement::TYPE_USAGE => 'Sử dụng',
            FoodStockMovement::TYPE_RETURN => 'Hoàn kho',
            FoodStockMovement::TYPE_ADJUSTMENT => 'Điều chỉnh',
            default => $type,
        };
    }

    private function unitLabel(FoodStockMovement $movement): ?string
    {
        if ($movement->unit_name_snapshot && $movement->unit_symbol_snapshot) {
            return "{$movement->unit_name_snapshot} ({$movement->unit_symbol_snapshot})";
        }

        return $movement->unit_symbol_snapshot ?? $movement->unit_name_snapshot;
    }
}
