<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $food_id
 * @property int $cooking_method_id
 * @property int $additional_price
 * @property int $status
 * @property string $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BillDetail> $billDetail
 * @property-read int|null $bill_detail_count
 * @property-read \App\Models\CookingMethod $cookingMethod
 * @property-read \App\Models\Food $food
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish whereAdditionalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish whereCookingMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish whereFoodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Dish extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'food_id',
        'cooking_method_id',
        'additional_price',
        'note',
    ];

    public function food()
    {
        return $this->belongsTo(Food::class);
    }

    public function cookingMethod()
    {
        return $this->belongsTo(CookingMethod::class);
    }

    public function billDetail()
    {
        return $this->hasMany(BillDetail::class);
    }
}
