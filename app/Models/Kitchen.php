<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $image
 * @property int $branch_id
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch $branch
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\KitchenCookingMethod> $cookingMethod
 * @property-read int|null $cooking_method_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Kitchen extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'branch_id',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function cookingMethods()
    {
        return $this->hasMany(KitchenCookingMethod::class);
    }
}
