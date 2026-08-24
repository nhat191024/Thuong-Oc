<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodStockMovement extends Model
{
    public const TYPE_RESTOCK = 'restock';

    public const TYPE_USAGE = 'usage';

    public const TYPE_RETURN = 'return';

    public const TYPE_ADJUSTMENT = 'adjustment';

    protected $fillable = [
        'branch_food_stock_id',
        'type',
        'quantity_in_kg',
        'balance_before_kg',
        'balance_after_kg',
        'bill_detail_id',
        'created_by_user_id',
        'note',
        'occurred_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_in_kg' => 'decimal:3',
            'balance_before_kg' => 'decimal:3',
            'balance_after_kg' => 'decimal:3',
            'occurred_at' => 'datetime',
        ];
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(BranchFoodStock::class, 'branch_food_stock_id');
    }

    public function billDetail(): BelongsTo
    {
        return $this->belongsTo(BillDetail::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
