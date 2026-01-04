<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Food;

use App\Enums\BillDetailStatus;

/**
 * @property int $id
 * @property int $bill_id
 * @property int|null $dish_id
 * @property string|null $custom_dish_name
 * @property int $quantity
 * @property int $price
 * @property string|null $note
 * @property int|null $custom_kitchen_id
 * @property BillDetailStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Bill|null $bill
 * @property-read \App\Models\Dish|null $dish
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillDetail whereBillId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillDetail whereCustomDishName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillDetail whereCustomKitchenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillDetail whereDishId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillDetail whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillDetail wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillDetail whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillDetail whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillDetail whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BillDetail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'bill_id',
        'dish_id',
        'custom_dish_name',
        'quantity',
        'price',
        'note',
        'custom_kitchen_id',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'status' => BillDetailStatus::class,
    ];

    //model boot method
    protected static function boot()
    {
        parent::boot();

        static::created(function ($billDetail) {
            if ($billDetail->dish) {
                $foodId = $billDetail->dish->food_id;
                $food = Food::findOrFail($foodId);
                if ($food) {
                    $food->increment('sold_count', $billDetail->quantity);
                }
            }
        });
    }

    //Model Relations
    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function dish()
    {
        return $this->belongsTo(Dish::class);
    }
}
