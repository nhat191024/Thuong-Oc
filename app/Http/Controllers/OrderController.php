<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\BillDetail;
use App\Models\Bill;
use App\Models\Voucher;
use App\Models\Customer;

use App\Enums\PayStatus;
use App\Enums\TableActiveStatus;
use App\Enums\PaymentMethods;

use App\Settings\AppSettings;

use App\Events\NewDishOrdered;

use Inertia\Inertia;

use App\Http\Requests\PlaceOrderRequest;

use Illuminate\Http\Request;

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
            $bill = $table->bill;
            $bill->customer_id = $request->customer_id;
            $bill->save();
        } else {
            $table->is_active = TableActiveStatus::ACTIVE;
            $table->save();

            $bill = Bill::create([
                'table_id' => $request->table_id,
                'branch_id' => $request->branch_id,
                'user_id' => $request->user_id,
                'customer_id' => $request->customer_id,
            ]);
        }

        $billTotal = $bill->total ?? 0;

        foreach ($request->input('dishes') as $dish) {
            $billDetail = BillDetail::create([
                'bill_id' => $bill->id,
                'dish_id' => $dish['dish_id'] ?? null,
                'custom_dish_name' => $dish['custom_dish_name'] ?? null,
                'custom_kitchen_id' => $dish['custom_kitchen_id'] ?? null,
                'quantity' => $dish['quantity'],
                'price' => $dish['price'],
                'note' => $dish['note'],
            ]);

            $billTotal += $dish['price'] * $dish['quantity'];

            $billDetail->load(['dish.food', 'bill.table']);
            broadcast(new NewDishOrdered($billDetail));
        }

        $bill->total = $billTotal;
        $bill->save();

        return redirect()->back()->with('success', 'Order placed successfully');
    }

    /**
     * Handle payment result
     *
     * @param \Illuminate\Http\Request $request
     * @return \Inertia\Response
     */
    public function paymentResult(Request $request)
    {
        $orderCode = $request->input('orderCode');
        $status = $request->input('status');
        $cancel = $request->input('cancel');

        if ($request->has('code')) {
            $code = $request->input('code');
            $id = $request->input('id');
            if ($code == '00' && $cancel != 'true') {
                $status = 'PAID';
                $bill = Bill::find($orderCode);
                if ($bill && $bill->pay_status !== PayStatus::PAID) {
                    $voucher = null;
                    $discountAmount = 0;
                    if ($bill->voucher_id) {
                        $voucher = Voucher::find($bill->voucher_id);
                        if ($voucher) {
                            $discountAmount = $voucher->getDiscountAmount($bill->total);
                        }
                    }

                    $bill->final_total = $bill->total - $discountAmount;
                    $bill->pay_status = PayStatus::PAID;
                    $bill->payment_method = PaymentMethods::QR_CODE;
                    $bill->time_out = now();
                    $bill->save();

                    $customer = $bill->customer_id ? Customer::find($bill->customer_id) : null;
                    if ($customer) {
                        $pointStep = app(AppSettings::class)->point_step;
                        $pointEarned = floor($bill->final_total / $pointStep);
                        $customer->points += $pointEarned;
                        $customer->save();
                    }

                    $table = $bill->table;
                    if ($table) {
                        $table->is_active = TableActiveStatus::INACTIVE;
                        $table->save();
                    }
                }
            } else {
                $status = 'CANCELLED';
            }
        }

        return Inertia::render('Payment/Result', [
            'orderCode' => $orderCode,
            'status' => $status,
        ]);
    }
}
