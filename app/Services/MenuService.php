<?php

namespace App\Services;

use App\Enums\CacheKeys;

use App\Models\Category;

use App\Http\Resources\CategoryResourceCollection;

use Illuminate\Support\Facades\Cache;

class MenuService
{
    /**
     * Get menus with categories and foods
     * @var bool $useCollection Whether to return as a resource collection
     *
     * @return CategoryResourceCollection|mixed
     */
    public function getMenus(bool $useCollection)
    {
        $menus = Cache::rememberForever(CacheKeys::MENUS->value . '-' . ($useCollection ? 'collection' : 'array'), function () use ($useCollection) {
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

            if ($useCollection) {
                return new CategoryResourceCollection($categories);
            } else {
                return $this->transformMenusToArray($categories);
            }
        });

        return $menus;
    }

    /**
     * Transform menus to array
     *
     * @param mixed $categories
     * @return array
     */
    private function transformMenusToArray($categories)
    {
        return $categories->map(
            fn($category) => [
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
            ]
        );
    }
}
