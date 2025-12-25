<?php

namespace App\Services;

use App\Enums\CacheKeys;

use App\Models\Category;

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

            return $categories->map(fn($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'foods' => $category->food->map(fn($food) => [
                    'id' => $food->id,
                    'name' => $food->name,
                    'is_favorite' => $food->is_favorite,
                    'is_discounted' => $food->discount_price > 0,
                    'price' => $food->price,
                    'discount_price' => $food->discount_price,
                    'note' => $food->note,
                    'image' => $food->getFirstMediaUrl('default', 'preview'),
                    'sold_count' => $food->sold_count,
                    'dishes' => $food->dishes->map(fn($dish) => [
                        'id' => $dish->id,
                        'name' => $dish->cookingMethod?->name,
                        'additional_price' => $dish->additional_price,
                        'note' => $dish->note,
                    ]),
                ]),
            ]);
        });

        return $menus;
    }
}
