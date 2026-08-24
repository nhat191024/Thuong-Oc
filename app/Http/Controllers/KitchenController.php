<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

use App\Models\Kitchen;
use App\Models\Branch;
use App\Models\BillDetail;
use App\Models\KitchenCookingMethod;

use App\Enums\PayStatus;
use App\Enums\BillDetailStatus;

use App\Services\KitchenPrintJobService;
use App\Services\FoodInventoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class KitchenController extends Controller
{
    /**
     * Show kitchen dashboard
     *
     * @return Response
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
     * Show kitchen details
     *
     * @return Response
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
     * @return RedirectResponse
     */
    public function updateStatus(Request $request, BillDetail $billDetail)
    {
        $request->validate([
            'status' => ['required', Rule::enum(BillDetailStatus::class)],
            'kitchen_id' => [
                'nullable',
                'integer',
                Rule::exists(Kitchen::class, 'id')
                    ->where('branch_id', $request->user()->branch_id),
            ],
        ]);

        $result = DB::transaction(function () use ($request, $billDetail): ?array {
            $lockedBillDetail = BillDetail::query()
                ->with(['dish', 'bill'])
                ->lockForUpdate()
                ->find($billDetail->id);

            if ($lockedBillDetail === null || $lockedBillDetail->bill === null) {
                return null;
            }

            if ($lockedBillDetail->bill->branch_id !== $request->user()->branch_id) {
                abort(403);
            }

            if ($lockedBillDetail->status === BillDetailStatus::CANCELLED) {
                return null;
            }

            $requestedStatus = $request->enum('status', BillDetailStatus::class);
            $transitionedToDone = $requestedStatus === BillDetailStatus::DONE
                && $lockedBillDetail->status !== BillDetailStatus::DONE;

            $lockedBillDetail->status = $requestedStatus;
            $lockedBillDetail->save();

            if ($transitionedToDone) {
                app(FoodInventoryService::class)->consumeCompletedBillDetail(
                    billDetail: $lockedBillDetail,
                    branchId: $lockedBillDetail->bill->branch_id,
                    createdByUserId: $request->user()->id,
                );
            }

            $lockedBillDetail->bill->recalculateTotal();

            return [
                'bill_detail' => $lockedBillDetail,
                'transitioned_to_done' => $transitionedToDone,
            ];
        });

        if ($result === null) {
            return back()->with('error', 'Món đã bị hủy bởi quản lý.');
        }

        /** @var BillDetail $billDetail */
        $billDetail = $result['bill_detail'];
        $billDetail->loadMissing(['dish.food', 'dish.cookingMethod', 'bill.table']);

        if ($result['transitioned_to_done']) {
            $kitchen = $this->resolveKitchenForBillDetail(
                $billDetail,
                $request->integer('kitchen_id') ?: null,
            );

            app(KitchenPrintJobService::class)->request($billDetail, $kitchen);
        }

        return back();
    }

    /**
     * Get kitchen history
     *
     * @return JsonResponse
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

    private function resolveKitchenForBillDetail(BillDetail $billDetail, ?int $kitchenId): ?Kitchen
    {
        $branchId = $billDetail->bill->branch_id;

        if ($kitchenId !== null) {
            return Kitchen::query()
                ->whereBranchId($branchId)
                ->whereKey($kitchenId)
                ->first();
        }

        if ($billDetail->custom_kitchen_id !== null) {
            return Kitchen::query()
                ->whereBranchId($branchId)
                ->whereKey($billDetail->custom_kitchen_id)
                ->first();
        }

        $cookingMethodId = $billDetail->dish?->cooking_method_id;

        if ($cookingMethodId === null) {
            return null;
        }

        return Kitchen::query()
            ->whereBranchId($branchId)
            ->whereHas('cookingMethods', fn ($query) => $query->where('cooking_methods.id', $cookingMethodId))
            ->first();
    }
}
