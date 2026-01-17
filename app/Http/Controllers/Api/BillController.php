<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentMethods;
use App\Enums\PayStatus;
use App\Enums\Role;
use App\Enums\TableActiveStatus;

use App\Http\Controllers\Controller;

use App\Http\Requests\ProcessPaymentRequest;
use App\Http\Resources\BillResource;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\Table;
use App\Models\Voucher;

use App\Services\PaymentService;

use App\Settings\AppSettings;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class BillController extends Controller
{
    public function show(string $tableId)
    {
        $table = Table::findOrFail($tableId);

        $bill = $table->bill()
            ->with(['billDetails.dish.food', 'billDetails.dish.cookingMethod', 'customer', 'table'])
            ->where('pay_status', PayStatus::UNPAID)
            ->first();

        if (!$bill) {
            return response()->json(['message' => 'No unpaid bill found for this table.'], 404);
        }

        return new BillResource($bill);
    }

    public function attachCustomer(Request $request, string $tableId)
    {
        $request->validate([
            'phone' => 'required|string',
            'name' => 'nullable|string',
        ]);

        $phone = $request->input('phone');
        $name = $request->input('name');

        $table = Table::findOrFail($tableId);
        $bill = $table->bill()->where('pay_status', PayStatus::UNPAID)->first();

        if (!$bill) {
            return response()->json(['message' => 'No unpaid bill found.'], 404);
        }

        $customer = Customer::where('phone', $phone)->first();

        if (!$customer) {
            if ($name) {
                if (Customer::where('username', $phone)->exists()) {
                    return response()->json(['message' => 'Account already exists.'], 409);
                }

                $customer = new Customer();
                $customer->name = $name;
                $customer->phone = $phone;
                $customer->username = $phone;
                $customer->password = Hash::make($phone);
                $customer->save();

                $customer->assignRole(Role::CUSTOMER);
            } else {
                return response()->json(['message' => 'Customer not found. Please provide name to create new.'], 404);
            }
        }

        $bill->customer_id = $customer->id;
        $bill->save();

        return response()->json(['message' => 'Customer attached successfully.', 'customer' => $customer]);
    }

    public function removeCustomer(string $tableId)
    {
        $table = Table::findOrFail($tableId);
        $bill = $table->bill()->where('pay_status', PayStatus::UNPAID)->first();

        if (!$bill) {
            return response()->json(['message' => 'No unpaid bill found.'], 404);
        }

        $bill->customer_id = null;
        $bill->save();

        return response()->json(['message' => 'Customer removed successfully.']);
    }

    public function applyDiscount(Request $request, string $tableId)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $code = $request->input('code');
        $table = Table::findOrFail($tableId);
        /** @var Bill $bill */
        $bill = $table->bill()->where('pay_status', PayStatus::UNPAID)->first();

        if (!$bill) {
            return response()->json(['message' => 'No unpaid bill found.'], 404);
        }

        $voucher = Voucher::redeemVoucher($bill->total, code: $code, userId: $bill->customer_id);

        if (!$voucher->status) {
            return response()->json(['message' => $voucher->message], 400);
        }

        $bill->voucher_id = $voucher->voucher_id ?? null;
        $bill->save();

        return response()->json([
            'message' => 'Discount applied successfully.',
            'discount_amount' => $voucher->discount_amount
        ]);
    }

    public function removeDiscount(string $tableId)
    {
        $table = Table::findOrFail($tableId);
        $bill = $table->bill()->where('pay_status', PayStatus::UNPAID)->first();

        if (!$bill) {
            return response()->json(['message' => 'No unpaid bill found.'], 404);
        }

        if (!$bill->voucher_id) {
            return response()->json(['message' => 'No discount applied.'], 400);
        }

        $bill->voucher_id = null;
        $bill->save();

        return response()->json(['message' => 'Discount removed successfully.']);
    }

    public function processPayment(ProcessPaymentRequest $request, string $tableId)
    {
        $paymentMethod = $request->input('payment_method');
        $table = Table::findOrFail($tableId);
        $bill = $table->bill()->with('billDetails')->where('pay_status', PayStatus::UNPAID)->first();

        if (!$bill) {
            return response()->json(['message' => 'No unpaid bill found.'], 404);
        }

        $discountAmount = 0;
        if ($bill->voucher_id) {
            $voucher = Voucher::find($bill->voucher_id);
            if ($voucher) {
                $discountAmount = $voucher->getDiscountAmount($bill->total);
            }
        }

        $finalTotal = $bill->total - $discountAmount;

        if ($paymentMethod === PaymentMethods::CASH->value) {
            $bill->pay_status = PayStatus::PAID;
            $bill->payment_method = PaymentMethods::CASH;
            $bill->time_out = now();
            $bill->final_total = $finalTotal;
            $bill->save();

            $table->is_active = TableActiveStatus::INACTIVE;
            $table->save();

            if ($bill->voucher_id) {
                $voucher = Voucher::find($bill->voucher_id);
                if ($voucher) {
                    $voucher->increment('used_count');
                }
            }

            $customer = $bill->customer_id ? Customer::find($bill->customer_id) : null;
            if ($customer) {
                $pointStep = app(AppSettings::class)->point_step;
                $pointEarned = floor($bill->final_total / $pointStep);
                $customer->points += $pointEarned;
                $customer->save();
            }

            return response()->json([
                'message' => 'Payment successful.',
                'amount' => $bill->final_total
            ]);
        } elseif ($paymentMethod === PaymentMethods::QR_CODE->value) {

            $items = $bill->billDetails->map(function ($detail) {
                $name = $detail->custom_dish_name;
                if ($detail->dish) {
                    $name = $detail->dish->food->name;
                }
                return [
                    'name' => $name,
                    'quantity' => $detail->quantity,
                    'price' => $detail->price,
                ];
            })->toArray();

            if ($discountAmount > 0) {
                $discountItems = [
                    'name' => 'Mã giảm giá',
                    'quantity' => 1,
                    'price' => $discountAmount,
                ];

                $items[] = $discountItems;
            }

            $total = $bill->total - $discountAmount;

            $timestamp = time();
            $billId = $bill->id . $timestamp;

            $data = [
                'billId' => $billId,
                'billCode' => (string)$bill->id,
                'amount' => (int)$total,
                'items' => $items,
                'buyerName' => 'Khách hàng',
            ];

            try {
                $paymentService = app(PaymentService::class);
                $paymentLink = $paymentService->processPayment(
                    $data,
                    'qr_transfer',
                    true
                );

                return response()->json([
                    'message' => 'Payment link created.',
                    'data' => $paymentLink
                ]);
            } catch (\Exception $e) {
                Log::error('Payment creation failed: ' . $e->getMessage());
                return response()->json(['message' => 'Failed to create payment link.'], 500);
            }
        }

        return response()->json(['message' => 'Invalid payment method.'], 400);
    }

    public function updateStatus(Request $request, string $tableId)
    {
        $request->validate([
            'status' => ['required', Rule::enum(PayStatus::class)],
            'payment_method' => ['nullable', Rule::enum(PaymentMethods::class)],
        ]);

        $status = $request->input('status');
        $table = Table::findOrFail($tableId);
        $bill = $table->bill()->where('pay_status', PayStatus::UNPAID)->first();

        if (!$bill) {
            return response()->json(['message' => 'No unpaid bill found.'], 404);
        }

        if ($status === PayStatus::PAID->value) {
            $discountAmount = 0;
            if ($bill->voucher_id) {
                $voucher = Voucher::find($bill->voucher_id);
                if ($voucher) {
                    $discountAmount = $voucher->getDiscountAmount($bill->total);
                    $voucher->increment('used_count');
                }
            }
            $finalTotal = $bill->total - $discountAmount;

            $bill->pay_status = PayStatus::PAID;
            $bill->payment_method = $request->input('payment_method', PaymentMethods::CASH->value);
            $bill->time_out = now();
            $bill->final_total = $finalTotal;
            $bill->save();

            $table->is_active = TableActiveStatus::INACTIVE;
            $table->save();

            $customer = $bill->customer_id ? Customer::find($bill->customer_id) : null;
            if ($customer) {
                $pointStep = app(AppSettings::class)->point_step;
                $pointEarned = floor($bill->final_total / $pointStep);
                $customer->points += $pointEarned;
                $customer->save();
            }
        } else {
            $bill->pay_status = $status;
            $bill->save();
        }

        return response()->json(['message' => 'Bill status updated successfully.']);
    }
}
