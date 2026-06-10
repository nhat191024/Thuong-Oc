<?php

namespace App\Http\Controllers\Api;

use App\Enums\PayStatus;

use App\Http\Controllers\Controller;
use App\Http\Resources\BillHistoryDetailResource;
use App\Http\Resources\BillHistoryResource;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;

use App\Models\Bill;
use App\Models\Table;

class BillHistoryController extends Controller
{
    public function index(Request $request, string $tableId): AnonymousResourceCollection
    {
        Table::findOrFail($tableId);

        $perPage = (int) $request->query('per_page', 10);

        $bills = Bill::query()
            ->where('table_id', $tableId)
            ->where('pay_status', PayStatus::PAID)
            ->orderByDesc('time_out')
            ->paginate($perPage);

        return BillHistoryResource::collection($bills);
    }

    public function show(string $tableId, string $billId): BillHistoryDetailResource
    {
        Table::findOrFail($tableId);

        $bill = Bill::query()
            ->where('table_id', $tableId)
            ->where('pay_status', PayStatus::PAID)
            ->where('id', $billId)
            ->with([
                'billDetails',
                'billDetails.dish.food',
                'billDetails.dish.cookingMethod',
                'customer',
                'table',
            ])
            ->firstOrFail();

        return new BillHistoryDetailResource($bill);
    }
}
