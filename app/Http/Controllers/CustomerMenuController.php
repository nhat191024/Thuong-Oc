<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Table;

use App\Enums\CacheKeys;
use App\Enums\TableActiveStatus;
use App\Enums\PayStatus;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Cache;


class CustomerMenuController extends Controller
{
    /**
     * Display the customer menu for the specified table.
     */
    public function index(string $tableId): Response
    {
        $table = Table::with([
            'bill' => function ($query) {
                $query->where('pay_status', PayStatus::UNPAID);
            },
            'bill.billDetails.dish.food',
            'bill.billDetails.dish.cookingMethod',
        ])->findOrFail($tableId);

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

        $categories = Cache::remember(CacheKeys::MENU_CATEGORIES->value, 3600, function () {
            return Category::select(['id', 'name'])
                ->has('food.dishes')
                ->orderBy('order')
                ->get()
                ->map(fn($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                ]);
        });

        $currentOrder = [];
        if ($table->is_active === TableActiveStatus::ACTIVE) {
            $bill = $table->bill;
            if ($bill) {
                $currentOrder = $bill->billDetails->map(fn($detail) => [
                    'table' => $table->table_number,
                    'foodId' => $detail->dish->food_id,
                    'dishId' => $detail->dish_id,
                    'name' => $detail->dish->food->name,
                    'quantity' => $detail->quantity,
                    'price' => $detail->price,
                    'cookingMethod' => $detail->dish->cookingMethod?->name,
                    'cookingMethodId' => $detail->dish->cooking_method_id,
                    'note' => $detail->note,
                ]);
            }
        }

        return Inertia::render('menu/index', [
            'menus' => $menus,
            'table' => $table,
            'categories' => $categories,
            'currentOrder' => $currentOrder,
        ]);
    }
}
