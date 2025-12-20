<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $food_id
 * @property int $cooking_method_id
 * @property int $additional_price
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BillDetail> $billDetails
 * @property-read int|null $bill_details_count
 * @property-read \App\Models\CookingMethod|null $cookingMethod
 * @property-read \App\Models\Food|null $food
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish whereAdditionalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish whereCookingMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish whereFoodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish withoutTrashed()
 * @mixin \Eloquent
 */
class Dish extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'food_id',
        'cooking_method_id',
        'additional_price',
        'note',
    ];

    //Model Relations
    public function food()
    {
        return $this->belongsTo(Food::class);
    }

    public function cookingMethod()
    {
        return $this->belongsTo(CookingMethod::class);
    }

    public function billDetails()
    {
        return $this->hasMany(BillDetail::class);
    }
}
