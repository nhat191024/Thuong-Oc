<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enums\BillDetailStatus;

/**
 * @property int $id
 * @property int $bill_id
 * @property int $dish_id
 * @property int $quantity
 * @property int $price
 * @property string|null $note
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
    protected $fillable = [
        'bill_id',
        'dish_id',
        'quantity',
        'price',
        'note',
        'status',
    ];

    protected $casts = [
        'status' => BillDetailStatus::class,
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function dish()
    {
        return $this->belongsTo(Dish::class);
    }
}
