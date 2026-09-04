<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\Services\MenuService;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

use Spatie\Image\Enums\Fit;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property int $price
 * @property int $discount_price
 * @property bool $is_favorite
 * @property string|null $note
 * @property int $sold_count
 * @property int $order
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, BranchFoodStock> $branchFoodStocks
 * @property-read int|null $branch_food_stocks_count
 * @property-read Category|null $category
 * @property-read Collection<int, Dish> $dishes
 * @property-read int|null $dishes_count
 * @property-read MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Food newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Food newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Food onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Food query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Food whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Food whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Food whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Food whereDiscountPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Food whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Food whereIsFavorite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Food whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Food whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Food whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Food wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Food whereSoldCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Food whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Food withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Food withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Food extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $table = 'foods';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'inventory_unit_id',
        'name',
        'price',
        'purchase_price',
        'discount_price',
        'is_favorite',
        'note',
        'sold_count',
        'order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'is_favorite' => 'boolean',
    ];

    /**
     * Register the media conversions.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Fit::Contain, 300, 300)
            ->queued();
    }

    // model boot method
    protected static function booted(): void
    {
        static::saved(function () {
            MenuService::forgetCache();
        });

        static::deleted(function () {
            MenuService::forgetCache();
        });

        static::updated(function () {
            MenuService::forgetCache();
        });

        static::created(function () {
            MenuService::forgetCache();
        });

        $clearCacheIfFood = function (Media $media): void {
            if ($media->model_type === static::class) {
                MenuService::forgetCache();
            }
        };

        Media::saved($clearCacheIfFood);
        Media::deleted($clearCacheIfFood);
    }

    // Model Relations
    public function dishes()
    {
        return $this->hasMany(Dish::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function branchFoodStocks()
    {
        return $this->hasMany(BranchFoodStock::class);
    }

    public function inventoryUnit(): BelongsTo
    {
        return $this->belongsTo(InventoryUnit::class);
    }
}
