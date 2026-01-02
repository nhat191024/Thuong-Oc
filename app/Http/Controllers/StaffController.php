<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

use App\Models\Table;
use App\Models\Branch;
use App\Models\Category;

use App\Enums\CacheKeys;
use App\Enums\TableActiveStatus;
use App\Enums\PayStatus;

use App\Http\Resources\TableResource;

use App\Services\MenuService;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class StaffController extends Controller
{
    protected MenuService $menuService;

    public function __construct()
    {
        $this->menuService = app(MenuService::class);
    }

    /**
     * Show staff dashboard
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $user = Auth::user();
        $branchId = $user->branch_id;
        $tables = Table::where('branch_id', $branchId)->get();
        $tables = TableResource::collection($tables)->resolve();
        $branchName = Branch::find($branchId)->name;

        return Inertia::render('staff/index', [
            'tables' => $tables,
            'branchName' => $branchName,
        ]);
    }

    /**
     * Show details for a specific table
     *
     * @param string $tableId
     * @return \Inertia\Response
     */
    public function showTable(string $tableId)
    {
        $table = Table::with([
            'bill' => function ($query) {
                $query->where('pay_status', PayStatus::UNPAID);
            },
            'bill.billDetails.dish.food',
            'bill.billDetails.dish.cookingMethod',
        ])->findOrFail($tableId);

        $table_number = $table->table_number;
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

        return Inertia::render('staff/tableDetail', [
            'table' => $table,
            'menus' => $menus,
            'categories' => $categories,
            'currentOrder' => $currentOrder,
        ]);
    }
}
