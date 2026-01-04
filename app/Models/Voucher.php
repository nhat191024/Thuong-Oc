<?php

namespace App\Models;

use Illuminate\Support\Carbon;

use BeyondCode\Vouchers\Models\Voucher as BaseVoucher;

/**
 * @property int $id
 * @property string $code
 * @property string $model_type
 * @property int $model_id
 * @property \Illuminate\Support\Collection<array-key, mixed>|null $data
 * @property Carbon|null $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $model
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voucher newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voucher newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voucher query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voucher whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voucher whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voucher whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voucher whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voucher whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voucher whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voucher whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Voucher whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Voucher extends BaseVoucher
{
    /**
     * Redeem the voucher
     *
     * @param string|null $code
     * @param int|null $id
     * @param int $orderTotal
     *
     * @return object { status: bool, message: string, discountAmount: int }
     */
    public static function redeemVoucher(int $orderTotal, ?string $code = null, ?int $id = null): object
    {
        $voucher = null;
        if ($id) {
            $voucher = self::find($id);
        } else {
            $voucher = self::where('code', $code)->first();
        }

        if (!$voucher) {
            return (object) [
                'status' => false,
                'message' => __('Mã giảm giá không tồn tại'),
                'discountAmount' => 0
            ];
        }

        $validation = $voucher->validate($orderTotal);
        if (!$validation->status) {
            return (object) [
                'status' => false,
                'message' => $validation->message,
                'discountAmount' => 0
            ];
        }

        $discountAmount = $voucher->getDiscountAmount($orderTotal);

        return (object) [
            'status' => true,
            'message' => __('Mã giảm giá hợp lệ'),
            'voucher_id' => $voucher->id,
            'discount_percent' => $voucher->discountPercentage(),
            'discount_amount' => $discountAmount
        ];
    }


    /**
     * Get the discount percentage from the voucher data
     *
     * @return int|null
     */
    private function discountPercentage(): ?int
    {
        return $this->data['discount_percent'] ?? null;
    }

    /**
     * Get the discount amount from the voucher data
     *
     * @return int|null
     */
    private function maxDiscountAmount(): ?int
    {
        return $this->data['max_discount_amount'] ?? null;
    }

    /**
     * Get the minimum order amount from the voucher data
     *
     * @return int|null
     */
    private function minOrderAmount(): ?int
    {
        return $this->data['min_order_amount'] ?? null;
    }

    /**
     * Get the usage limit from the voucher data
     *
     * @return int|null
     */
    private function usageLimit(): ?int
    {
        return $this->data['usage_limit'] ?? null;
    }

    /**
     * Get the times used from the voucher data
     *
     * @return int
     */
    private function timesUsed(): int
    {
        return $this->data['times_used'] ?? 0;
    }

    /**
     * Check if the voucher is unlimited from the voucher data
     *
     * @return bool
     */
    private function isUnlimited(): bool
    {
        return $this->data['is_unlimited'] ?? false;
    }

    /**
     * Get the start at date from the voucher data
     *
     * @return Carbon|null
     */
    private function startAt(): ?Carbon
    {
        return isset($this->data['starts_at']) ? Carbon::parse($this->data['starts_at']) : null;
    }

    /**
     * Validate the voucher against the order total and other conditions
     *
     * @param int $orderTotal
     *
     * @return object { status: bool, message: string }
     */
    private function validate($orderTotal): object
    {
        $now = Carbon::now();

        if ($this->expires_at && $now->greaterThan($this->expires_at)) {
            return (object) [
                'status' => false,
                'message' => __('Mã giảm giá đã hết hạn')
            ];
        }

        if ($this->startAt() && $now->lessThan($this->startAt())) {
            return (object) [
                'status' => false,
                'message' => __('Mã giảm giá chưa có hiệu lực')
            ];
        }

        if (!$this->isUnlimited()) {
            if ($this->usageLimit() !== null && $this->timesUsed() >= $this->usageLimit()) {
                return (object) [
                    'status' => false,
                    'message' => __('Mã giảm giá đã đạt giới hạn sử dụng')
                ];
            }
        }

        if ($this->minOrderAmount() !== null && $orderTotal < $this->minOrderAmount()) {
            return (object) [
                'status' => false,
                'message' => __('Mã giảm giá chưa đạt giá trị đơn hàng tối thiểu')
            ];
        }

        return (object) [
            'status' => true,
            'message' => __('Mã giảm giá hợp lệ')
        ];
    }

    /**
     * Calculate the discount amount based on the order total and voucher settings
     *
     * @param int $orderTotal
     * @return int
     */
    private function getDiscountAmount($orderTotal): int
    {
        $discount = 0;

        if ($this->discountPercentage() !== null) {
            $discount = $orderTotal * ($this->discountPercentage() / 100);

            if ($this->maxDiscountAmount() !== null) {
                $discount = min($discount, $this->maxDiscountAmount());
            }
        }

        return (int) round($discount);
    }
}
