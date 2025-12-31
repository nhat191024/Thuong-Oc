<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\BillDetail;
use App\Models\Bill;

use App\Enums\PayStatus;
use App\Enums\TableActiveStatus;

use App\Http\Requests\PlaceOrderRequest;

class OrderController extends Controller
{

    /**
     * Place an order for a table
     *
     * @param PlaceOrderRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function placeOrder(PlaceOrderRequest $request)
    {
        $table = Table::with([
            'bill' => function ($query) {
                $query->where('pay_status', PayStatus::UNPAID);
            }
        ])->find($request->input('table_id'));

        if ($table->is_active === TableActiveStatus::ACTIVE) {
            //If table is occupied update current bill with new order
            $bill = $table->bill;
        } else {
            //If table is free, create new bill and add order
            $table->is_active = TableActiveStatus::ACTIVE;
            $table->save();

            $bill = Bill::create([
                'table_id' => $request->table_id,
                'branch_id' => $request->branch_id,
                'user_id' => $request->user_id,
            ]);
        }

        $billTotal = $bill->total ?? 0;

        foreach ($request->input('dishes') as $dish) {
            $existingDetail = BillDetail::where('bill_id', $bill->id)
                ->where('dish_id', $dish['dish_id'])
                ->where('note', $dish['note'])
                ->first();

            if ($existingDetail) {
                $existingDetail->quantity += $dish['quantity'];
                $existingDetail->save();
            } else {
                BillDetail::create([
                    'bill_id' => $bill->id,
                    'dish_id' => $dish['dish_id'],
                    'quantity' => $dish['quantity'],
                    'price' => $dish['price'],
                    'note' => $dish['note'],
                ]);
            }

            $billTotal += $dish['price'] * $dish['quantity'];

            //TODO: add event new dish ordered to notify kitchen
        }

        $bill->total = $billTotal;
        $bill->save();

        return redirect()->back()->with('success', 'Order placed successfully');
    }
}
