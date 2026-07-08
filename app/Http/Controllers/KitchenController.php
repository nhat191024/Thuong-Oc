<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

use App\Models\Kitchen;
use App\Models\Branch;
use App\Models\BillDetail;
use App\Models\KitchenCookingMethod;
use App\Models\Printer;

use App\Enums\PayStatus;
use App\Enums\BillDetailStatus;

use App\Events\DishPrintRequested;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KitchenController extends Controller
{
    /**
     * Show kitchen dashboard
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $user = Auth::user();
        $branchId = $user->branch_id;
        $kitchens = Kitchen::whereBranchId($branchId)->get();
        $branchName = Branch::whereId($branchId)->value('name');

        return Inertia::render('kitchen/index', [
            'kitchens' => $kitchens,
            'branchName' => $branchName,
        ]);
    }

    /**
     * Show print station.
     *
     * @return \Inertia\Response
     */
    public function printStation(): \Inertia\Response
    {
        $user = Auth::user();
        $branchId = $user->branch_id;

        return Inertia::render('kitchen/print-station', [
            'branchId' => $branchId,
            'branchName' => Branch::whereId($branchId)->value('name'),
            'printers' => Printer::whereBranchId($branchId)
                ->where('is_active', true)
                ->get(['id', 'name']),
        ]);
    }

    /**
     * Show kitchen details
     *
     * @param  Kitchen  $kitchen
     * @return \Inertia\Response
     */
    public function show(Kitchen $kitchen)
    {
        $user = Auth::user();
        if ($kitchen->branch_id !== $user->branch_id) {
            abort(403);
        }

        $cookingMethodIds = KitchenCookingMethod::whereKitchenId($kitchen->id)
            ->pluck('cooking_method_id');

        $billDetails = BillDetail::query()
            ->with(['dish.food', 'dish.cookingMethod', 'bill.table'])
            ->whereHas('bill', function ($query) use ($kitchen) {
                $query->where('branch_id', $kitchen->branch_id)
                    ->where('pay_status', PayStatus::UNPAID);
            })
            ->where(function ($query) use ($cookingMethodIds, $kitchen) {
                $query->whereHas('dish', function ($q) use ($cookingMethodIds) {
                    $q->whereIn('cooking_method_id', $cookingMethodIds);
                })
                    ->orWhere('custom_kitchen_id', $kitchen->id);
            })
            ->where('status', BillDetailStatus::WAITING->value)
            ->orderBy('created_at', 'asc')
            ->get();

        return Inertia::render('kitchen/show', [
            'kitchen' => $kitchen,
            'billDetails' => $billDetails,
            'cookingMethodIds' => $cookingMethodIds,
        ]);
    }

    /**
     * Update bill detail status
     *
     * @param  Request  $request
     * @param  BillDetail  $billDetail
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, BillDetail $billDetail)
    {
        $request->validate([
            'status' => ['required', Rule::enum(BillDetailStatus::class)],
        ]);

        $billDetail->update([
            'status' => $request->status,
        ]);

        $billDetail->loadMissing(['dish.food', 'dish.cookingMethod', 'bill.table']);

        $bill = $billDetail->bill;
        $bill->recalculateTotal();

        if ($request->status === BillDetailStatus::DONE->value) {
            broadcast(new DishPrintRequested($billDetail));
        }

        return back();
    }

    /**
     * Get kitchen history
     *
     * @param  Request  $request
     * @param  Kitchen  $kitchen
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request, Kitchen $kitchen)
    {
        $user = Auth::user();
        if ($kitchen->branch_id !== $user->branch_id) {
            abort(403);
        }

        $cookingMethodIds = KitchenCookingMethod::whereKitchenId($kitchen->id)
            ->pluck('cooking_method_id');

        $history = BillDetail::query()
            ->with(['dish.food', 'dish.cookingMethod', 'bill.table'])
            ->whereHas('bill', function ($query) use ($kitchen) {
                $query->where('branch_id', $kitchen->branch_id);
            })
            ->where(function ($query) use ($cookingMethodIds, $kitchen) {
                $query->whereHas('dish', function ($q) use ($cookingMethodIds) {
                    $q->whereIn('cooking_method_id', $cookingMethodIds);
                })
                    ->orWhere('custom_kitchen_id', $kitchen->id);
            })
            ->whereIn('status', [BillDetailStatus::DONE->value, BillDetailStatus::CANCELLED->value])
            ->orderBy('updated_at', 'desc')
            ->paginate(9);

        return response()->json($history);
    }
}
