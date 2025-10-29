<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enums\PaymentMethods;
use App\Enums\PayStatus;

/**
 * @property int $id
 * @property string $table_id
 * @property int $branch_id
 * @property int|null $user_id
 * @property string $time_in
 * @property string|null $time_out
 * @property int $input_discount_amount
 * @property PaymentMethods $payment_method
 * @property int $total
 * @property PayStatus $pay_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BillDetail> $billDetail
 * @property-read int|null $bill_detail_count
 * @property-read \App\Models\Table $table
 * @property-read \App\Models\User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereInputDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill wherePayStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereTimeIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereTimeOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Bill extends Model
{
    protected $fillable = [
        'table_id',
        'branch_id',
        'user_id',
        'time_in',
        'time_out',
        'total',
        'discount',
        'final_total',
        'payment_method',
        'pay_status',
    ];

    protected $casts = [
        'payment_method' => PaymentMethods::class,
        'pay_status' => PayStatus::class,
    ];

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function billDetails()
    {
        return $this->hasMany(BillDetail::class);
    }
}
