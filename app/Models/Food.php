<?php

namespace App\Models;

use App\Enums\CacheKeys;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

use Spatie\Image\Enums\Fit;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Dish> $dishes
 * @property-read int|null $dishes_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $media
 * @property-read int|null $media_count
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
 * @mixin \Eloquent
 */
class Food extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $table = 'foods';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'price',
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

    //model boot method
    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget(CacheKeys::MENUS->value);
        });

        static::deleted(function () {
            Cache::forget(CacheKeys::MENUS->value);
        });
    }

    //Model Relations
    public function dishes()
    {
        return $this->hasMany(Dish::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
