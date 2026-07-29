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
use App\Http\Requests\PrintKitchenDishRequest;
use App\Http\Requests\UpdateKitchenPrintSettingsRequest;
use App\Services\KitchenPrintJobService;
use Illuminate\Support\Facades\Auth;
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
        $kitchens = Kitchen::with('printer')
            ->whereBranchId($branchId)
            ->get();
        $branchName = Branch::whereId($branchId)->value('name');

        return Inertia::render('kitchen/index', [
            'kitchens' => $kitchens,
            'branchName' => $branchName,
            'printers' => Printer::whereBranchId($branchId)
                ->where('is_active', true)
                ->get(['id', 'name']),
        ]);
    }

    /**
     * Show print station.
     */
    public function printStation(): Response
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
            'kitchen' => $kitchen->loadMissing('printer'),
            'billDetails' => $billDetails,
            'cookingMethodIds' => $cookingMethodIds,
            'printers' => Printer::whereBranchId($kitchen->branch_id)
                ->where('is_active', true)
                ->get(['id', 'name']),
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
            'kitchen_id' => ['nullable', 'integer'],
        ]);

        if (! $billDetail->bill()->exists()) {
            return back()->with('error', 'Đơn đã bị xóa, không thể cập nhật món.');
        }

        $wasUpdated = BillDetail::query()
            ->whereKey($billDetail->id)
            ->where('status', '!=', BillDetailStatus::CANCELLED->value)
            ->update(['status' => $request->status]);

        if ($wasUpdated === 0) {
            return back()->with('error', 'Món đã bị hủy bởi quản lý.');
        }

        $billDetail->refresh()->loadMissing(['dish.food', 'dish.cookingMethod', 'bill.table']);

        $bill = $billDetail->bill;
        $bill->recalculateTotal();

        if ($request->status === BillDetailStatus::DONE->value) {
            $printed = $this->autoPrintCompletedDish($billDetail, $request->integer('kitchen_id'));

            broadcast(new DishPrintRequested($billDetail, $printed));
        }

        return back();
    }

    /**
     * Update kitchen print settings.
     */
    public function updatePrintSettings(UpdateKitchenPrintSettingsRequest $request, Kitchen $kitchen): JsonResponse
    {
        $kitchen->update([
            'auto_print' => $request->boolean('auto_print'),
            'printer_id' => $request->integer('printer_id') ?: null,
        ]);

        return response()->json([
            'kitchen' => $kitchen->fresh('printer'),
            'message' => 'Đã lưu cài đặt in.',
        ]);
    }

    /**
     * Print a completed dish from the print station.
     */
    public function printBillDetail(PrintKitchenDishRequest $request, BillDetail $billDetail): JsonResponse
    {
        if ($billDetail->status !== BillDetailStatus::DONE) {
            return response()->json([
                'printed' => false,
                'message' => 'Chỉ có thể in món đã hoàn thành.',
            ], 422);
        }

        $job = app(KitchenPrintJobService::class)->request($billDetail);

        return response()->json([
            'queued' => true,
            'request_id' => $job['request_id'],
            'message' => 'Đã gửi lệnh in đến trạm in.',
        ], 202);
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

    private function autoPrintCompletedDish(BillDetail $billDetail, ?int $kitchenId): bool
    {
        $kitchen = $this->resolveKitchenForBillDetail($billDetail, $kitchenId);

        if (! $kitchen?->auto_print) {
            return false;
        }

        app(KitchenPrintJobService::class)->request($billDetail);

        return true;
    }

    private function resolveKitchenForBillDetail(BillDetail $billDetail, ?int $kitchenId): ?Kitchen
    {
        $branchId = $billDetail->bill->branch_id;

        if ($kitchenId) {
            return Kitchen::query()
                ->whereBranchId($branchId)
                ->whereKey($kitchenId)
                ->first();
        }

        if ($billDetail->custom_kitchen_id) {
            return Kitchen::query()
                ->whereBranchId($branchId)
                ->whereKey($billDetail->custom_kitchen_id)
                ->first();
        }

        $cookingMethodId = $billDetail->dish?->cooking_method_id;

        if (! $cookingMethodId) {
            return null;
        }

        return Kitchen::query()
            ->whereBranchId($branchId)
            ->whereHas('cookingMethods', fn ($query) => $query->where('cooking_methods.id', $cookingMethodId))
            ->first();
    }
}
