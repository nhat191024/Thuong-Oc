<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

use App\Models\Kitchen;
use App\Models\Branch;
use App\Models\BillDetail;
use App\Models\KitchenCookingMethod;

use App\Enums\PayStatus;
use App\Enums\BillDetailStatus;

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
        $kitchens = Kitchen::where('branch_id', $branchId)->get();
        $branchName = Branch::find($branchId)->name;

        return Inertia::render('kitchen/index', [
            'kitchens' => $kitchens,
            'branchName' => $branchName,
        ]);
    }

    /**
     * Show kitchen details
     *
     * @param  \App\Models\Kitchen  $kitchen
     * @return \Inertia\Response
     */
    public function show(Kitchen $kitchen)
    {
        $user = Auth::user();
        if ($kitchen->branch_id !== $user->branch_id) {
            abort(403);
        }

        $cookingMethodIds = KitchenCookingMethod::where('kitchen_id', $kitchen->id)
            ->pluck('cooking_method_id');

        $billDetails = BillDetail::query()
            ->with(['dish.food', 'bill.table'])
            ->whereHas('bill', function ($query) use ($kitchen) {
                $query->where('branch_id', $kitchen->branch_id)
                    ->where('pay_status', PayStatus::UNPAID);
            })
            ->whereHas('dish', function ($query) use ($cookingMethodIds) {
                $query->whereIn('cooking_method_id', $cookingMethodIds);
            })
            ->where('status', BillDetailStatus::WAITING->value)
            ->orderBy('created_at', 'asc')
            ->get();

        return Inertia::render('kitchen/show', [
            'kitchen' => $kitchen,
            'billDetails' => $billDetails,
        ]);
    }

    /**
     * Update bill detail status
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BillDetail  $billDetail
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

        return back();
    }
}
