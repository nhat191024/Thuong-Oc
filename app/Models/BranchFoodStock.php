<?php

namespace App\Models;

use App\Services\MenuService;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $branch_id
 * @property int $food_id
 * @property bool $is_out_of_stock
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Branch|null $branch
 * @property-read Food|null $food
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchFoodStock newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchFoodStock newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchFoodStock query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchFoodStock whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchFoodStock whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchFoodStock whereFoodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchFoodStock whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchFoodStock whereIsOutOfStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchFoodStock whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class BranchFoodStock extends Model
{
    protected $fillable = [
        'branch_id',
        'food_id',
        'is_out_of_stock',
        'is_inventory_tracked',
        'quantity_in_kg',
        'last_restocked_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_out_of_stock' => 'boolean',
            'is_inventory_tracked' => 'boolean',
            'quantity_in_kg' => 'decimal:3',
            'last_restocked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (BranchFoodStock $model): void {
            MenuService::forgetBranchStockCache($model->branch_id);
        });

        static::deleted(function (BranchFoodStock $model): void {
            MenuService::forgetBranchStockCache($model->branch_id);
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(FoodStockMovement::class);
    }
}
