<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $kitchen_id
 * @property int $cooking_method_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CookingMethod> $cookingMethod
 * @property-read int|null $cooking_method_count
 * @property-read \App\Models\Kitchen $kitchen
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenCookingMethod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenCookingMethod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenCookingMethod query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenCookingMethod whereCookingMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenCookingMethod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenCookingMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenCookingMethod whereKitchenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitchenCookingMethod whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class KitchenCookingMethod extends Model
{
    protected $table = 'kitchen_cooking_method';

    protected $fillable = [
        'kitchen_id',
        'cooking_method_id',
    ];

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function cookingMethod()
    {
        return $this->belongsToMany(CookingMethod::class);
    }
}
