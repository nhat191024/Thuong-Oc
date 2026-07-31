<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int $branch_id
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Branch|null $branch
 * @property-read Collection<int, CookingMethod> $cookingMethods
 * @property-read int|null $cooking_methods_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kitchen withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Kitchen extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'branch_id',
    ];

    // Model Relations
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function cookingMethods()
    {
        return $this->belongsToMany(CookingMethod::class, 'kitchen_cooking_method');
    }
}
