<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

use App\Models\Table;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Kitchen;
use App\Models\Voucher;

use App\Enums\CacheKeys;
use App\Enums\TableActiveStatus;
use App\Enums\PayStatus;
use App\Enums\PaymentMethods;

use App\Http\Resources\TableResource;

use App\Services\MenuService;
use App\Services\PaymentService;

use App\Http\Requests\ProcessPaymentRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class StaffController extends Controller
{
    protected MenuService $menuService;
    protected PaymentService $paymentService;

    public function __construct()
    {
        $this->menuService = app(MenuService::class);
        $this->paymentService = app(PaymentService::class);
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

        $currentOrder = $this->formatCurrentOrder($table);

        $inactiveTables = Table::where('branch_id', $table->branch_id)
            ->where('is_active', TableActiveStatus::INACTIVE)
            ->orderBy('table_number')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'table_number' => $t->table_number,
            ]);

        $kitchens = Kitchen::where('branch_id', $table->branch_id)
            ->select(['id', 'name'])
            ->get();

        return Inertia::render('staff/tableDetail', [
            'table' => $table,
            'menus' => $menus,
            'categories' => $categories,
            'currentOrder' => $currentOrder,
            'inactiveTables' => $inactiveTables,
            'kitchens' => $kitchens,
        ]);
    }

    /**
     * Format current order for a table
     *
     * @param Table $table
     * @return array
     */
    private function formatCurrentOrder($table): array
    {
        if ($table->is_active !== TableActiveStatus::ACTIVE || !$table->bill) {
            return [];
        }

        return $table->bill->billDetails
            ->groupBy(function ($detail) {
                if ($detail->dish_id) {
                    return "dish_{$detail->dish_id}_{$detail->note}";
                }
                return "custom_{$detail->custom_dish_name}_{$detail->price}_{$detail->note}";
            })
            ->map(function ($details) use ($table) {
                $detail = $details->first();
                /** @var \App\Models\BillDetail $detail */

                if ($detail->dish_id && $detail->dish) {
                    return [
                        'table' => $table->table_number,
                        'foodId' => $detail->dish->food_id,
                        'dishId' => $detail->dish_id,
                        'custom_dish_name' => null,
                        'name' => $detail->dish->food->name,
                        'quantity' => $details->sum('quantity'),
                        'price' => $detail->price,
                        'cookingMethod' => $detail->dish->cookingMethod?->name,
                        'cookingMethodId' => $detail->dish->cooking_method_id,
                        'note' => $detail->note,
                    ];
                }

                return [
                    'table' => $table->table_number,
                    'foodId' => null,
                    'dishId' => null,
                    'custom_dish_name' => $detail->custom_dish_name,
                    'name' => $detail->custom_dish_name,
                    'quantity' => $details->sum('quantity'),
                    'price' => $detail->price,
                    'cookingMethod' => null,
                    'cookingMethodId' => null,
                    'note' => $detail->note,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Show bill for a specific table
     *
     * @param string $tableId
     * @return \Inertia\Response
     */
    public function showBill(string $tableId)
    {
        $table = Table::with([
            'bill' => function ($query) {
                $query->where('pay_status', PayStatus::UNPAID);
            },
            'bill.user',
            'bill.billDetails.dish.food',
            'bill.billDetails.dish.cookingMethod',
        ])->findOrFail($tableId);

        $billDetails = [];
        $totalAmount = 0;

        if ($table->is_active === TableActiveStatus::ACTIVE && $table->bill) {
            $billDetails = $table->bill->billDetails->map(fn($detail) => [
                'name' => $detail->dish ? $detail->dish->food->name : $detail->custom_dish_name,
                'quantity' => $detail->quantity,
                'price' => $detail->price,
                'total' => $detail->quantity * $detail->price,
                'cookingMethod' => $detail->dish ? $detail->dish->cookingMethod?->name : null,
                'note' => $detail->note,
            ]);

            $totalAmount = $billDetails->sum('total');
        }

        $paymentMethods = collect(PaymentMethods::cases())->map(fn($method) => [
            'value' => $method->value,
            'label' => $method->label(),
        ]);

        return Inertia::render('staff/bill', [
            'table' => $table,
            'billDetails' => $billDetails,
            'totalAmount' => $totalAmount,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Apply discount code for a specific table
     *
     * @param Request $request
     * @param string $tableId

     */
    public function applyDiscount(Request $request, string $tableId)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $code = $request->input('code');
        $table = Table::findOrFail($tableId);

        /** @var \App\Models\Bill $bill */
        $bill = $table->bill()->where('pay_status', PayStatus::UNPAID)->first();

        if (!$bill) {
            return redirect()->back()->with('error', 'Không tìm thấy hóa đơn chưa thanh toán.');
        }

        $voucher = Voucher::redeemVoucher($bill->total, code: $code);

        if (!$voucher->status) {
            return redirect()->back()->with('error', $voucher->message);
        }

        $bill->voucher_id = $voucher->voucher_id ?? null;
        $bill->save();

        return redirect()->back()
            ->with('success', 'Áp dụng mã giảm giá thành công.')
            ->with('payload', [
                'discount_percent' => $voucher->discount_percent,
                'discount_amount' => $voucher->discount_amount,
            ]);
    }

    /**
     * Remove discount code for a specific table
     *
     * @param string $tableId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeDiscount(string $tableId)
    {
        $table = Table::findOrFail($tableId);
        $bill = $table->bill()->where('pay_status', PayStatus::UNPAID)->first();

        if (!$bill) {
            return redirect()->back()->with('error', 'Không tìm thấy hóa đơn chưa thanh toán.');
        }

        $bill->voucher_id = null;
        $bill->save();

        return redirect()->back()->with('success', 'Đã hủy mã giảm giá thành công.');
    }

    /**
     * Process payment for a specific table
     *
     * @param ProcessPaymentRequest $requests
     * @param string $tableId
     * @return \Illuminate\Http\RedirectResponse|\Inertia\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function processPayment(ProcessPaymentRequest $requests, string $tableId)
    {
        $paymentMethod = $requests->input('payment_method');
        $table = Table::findOrFail($tableId);
        $bill = $table->bill()->where('pay_status', PayStatus::UNPAID)->first();

        if (!$bill) {
            return redirect()->back()->with('error', 'Không tìm thấy hóa đơn chưa thanh toán cho bàn này.');
        }

        if ($paymentMethod === PaymentMethods::CASH->value) {
            $bill->pay_status = PayStatus::PAID;
            $bill->payment_method = PaymentMethods::CASH;
            $bill->time_out = now();
            $bill->save();

            $table->is_active = TableActiveStatus::INACTIVE;
            $table->save();

            return redirect()->route('payment.result', [
                'orderCode' => $bill->id,
                'status' => 'PAID',
                'amount' => $bill->final_total ?? $bill->total
            ]);
        } elseif ($paymentMethod === PaymentMethods::QR_CODE->value) {
            $items = $bill->billDetails->map(function ($detail) {
                return [
                    'name' => $detail->dish->food->name,
                    'quantity' => $detail->quantity,
                    'price' => $detail->price,
                ];
            })->toArray();

            $data = [
                'billId' => $bill->id,
                'billCode' => (string)$bill->id,
                'amount' => (int)$bill->total,
                'items' => $items,
                'buyerName' => 'Khách hàng',
            ];

            try {
                $response = $this->paymentService->processAppointmentPayment(
                    $data,
                    'qr_transfer',
                    false,
                    route('payment.result'),
                    route('payment.result')
                );

                if (isset($response['checkoutUrl'])) {
                    return Inertia::location($response['checkoutUrl']);
                }

                return redirect()->back()->with('error', 'Failed to create payment link.');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }

        return redirect()->back();
    }

    /**
     * Move bill to another table
     *
     * @param Request $request
     * @param string $tableId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function moveTable(Request $request, string $tableId)
    {
        $request->validate([
            'new_table_id' => 'required|exists:tables,id',
        ]);

        $newTableId = $request->input('new_table_id');
        $currentTable = Table::findOrFail($tableId);
        $newTable = Table::findOrFail($newTableId);

        if ($newTable->is_active === TableActiveStatus::ACTIVE) {
            return redirect()->back()->with('error', 'Bàn mới đang hoạt động, không thể chuyển.');
        }

        $bill = $currentTable->bill()->where('pay_status', PayStatus::UNPAID)->first();

        if (!$bill) {
            return redirect()->back()->with('error', 'Bàn hiện tại không có hóa đơn để chuyển.');
        }

        // Update bill
        $bill->table_id = $newTableId;
        $bill->save();

        // Update tables status
        $currentTable->is_active = TableActiveStatus::INACTIVE;
        $currentTable->save();

        $newTable->is_active = TableActiveStatus::ACTIVE;
        $newTable->save();

        return redirect()->route('staff.table.show', $newTableId)->with('success', 'Chuyển bàn thành công.');
    }
}
