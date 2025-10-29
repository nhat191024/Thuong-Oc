<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string|null $note
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Dish> $dish
 * @property-read int|null $dish_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\KitchenCookingMethod> $kitchen
 * @property-read int|null $kitchen_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookingMethod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookingMethod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookingMethod onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookingMethod query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookingMethod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookingMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookingMethod whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookingMethod whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookingMethod whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookingMethod whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookingMethod withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookingMethod withoutTrashed()
 *
 * @mixin \Eloquent
 */
class CookingMethod extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'note',
    ];

    public function dish()
    {
        return $this->hasMany(Dish::class);
    }

    public function kitchen()
    {
        return $this->hasMany(KitchenCookingMethod::class);
    }
}
