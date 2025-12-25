<?php

namespace App\Services;

use App\Enums\CacheKeys;

use App\Models\Category;

use App\Http\Resources\CategoryResourceCollection;

use Illuminate\Support\Facades\Cache;

class MenuService
{
    public function getMenus()
    {
        $menus = Cache::rememberForever(CacheKeys::MENUS->value, function () {
            $categories = Category::select(['id', 'name'])
                ->has('food.dishes')
                ->with([
                    'food' => function ($query) {
                        $query->select(['id', 'category_id', 'name', 'price', 'discount_price', 'is_favorite', 'note', 'sold_count'])
                            ->has('dishes')
                            ->with([
                                'media',
                                'dishes:id,food_id,cooking_method_id,additional_price,note',
                                'dishes.cookingMethod:id,name',
                            ])
                            ->orderBy('order');
                    },
                ])
                ->orderBy('order')
                ->get();

            return new CategoryResourceCollection($categories);
        });

        return $menus;
    }
}
