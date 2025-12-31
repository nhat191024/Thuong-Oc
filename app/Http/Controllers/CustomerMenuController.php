<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Table;

use App\Enums\CacheKeys;
use App\Enums\TableActiveStatus;
use App\Enums\PayStatus;

use App\Services\MenuService;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Cache;

class CustomerMenuController extends Controller
{
    protected MenuService $menuService;

    public function __construct()
    {
        $this->menuService = app(MenuService::class);
    }

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

        $menus = $this->menuService->getMenus(useCollection: false);

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
