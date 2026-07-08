<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $summary_date
 * @property int $dish_id
 * @property int $food_id
 * @property int $cooking_method_id
 * @property string $food_name
 * @property string $cooking_method_name
 * @property int $total_quantity
 * @property int $total_revenue
 * @property \Illuminate\Support\Carbon|null $last_ordered_at
 * @property \Illuminate\Support\Carbon $calculated_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CookingMethod|null $cookingMethod
 * @property-read \App\Models\Dish|null $dish
 * @property-read \App\Models\Food|null $food
 * @mixin \Eloquent
 */
class DishSalesSummary extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'summary_date',
        'dish_id',
        'food_id',
        'cooking_method_id',
        'food_name',
        'cooking_method_name',
        'total_quantity',
        'total_revenue',
        'last_ordered_at',
        'calculated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'summary_date' => 'date',
        'last_ordered_at' => 'datetime',
        'calculated_at' => 'datetime',
    ];

    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }

    public function cookingMethod(): BelongsTo
    {
        return $this->belongsTo(CookingMethod::class);
    }
}
