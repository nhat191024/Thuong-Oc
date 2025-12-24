<?php

namespace App\Models;

use App\Enums\CacheKeys;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $name
 * @property string|null $note
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Food> $food
 * @property-read int|null $food_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withoutTrashed()
 * @mixin \Eloquent
 */
class Category extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'note',
        'order',
    ];

    //model boot method
    protected static function booted(): void
    {
        static::deleting(function (Category $category) {
            $category->food()->delete();
        });

        static::restoring(function (Category $category) {
            $category->food()->withTrashed()->restore();
        });

        static::forceDeleted(function (Category $category) {
            $category->food()->withTrashed()->forceDelete();
        });

        static::created(function () {
            Cache::forget(CacheKeys::MENUS->value);
            Cache::forget(CacheKeys::MENU_CATEGORIES->value);
        });

        static::updated(function () {
            Cache::forget(CacheKeys::MENUS->value);
            Cache::forget(CacheKeys::MENU_CATEGORIES->value);
        });
    }

    //Model Relations
    public function food()
    {
        return $this->hasMany(Food::class);
    }
}
