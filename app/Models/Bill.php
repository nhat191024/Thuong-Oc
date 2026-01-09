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
 * @property int|null $customer_id
 * @property string $time_in
 * @property string|null $time_out
 * @property int $total
 * @property int|null $discount
 * @property int $final_total
 * @property PaymentMethods|null $payment_method
 * @property PayStatus $pay_status
 * @property int|null $voucher_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BillDetail> $billDetails
 * @property-read int|null $bill_details_count
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\Customer|null $customer
 * @property-read \App\Models\Table|null $table
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereFinalTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill wherePayStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereTimeIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereTimeOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereVoucherId($value)
 * @mixin \Eloquent
 */
class Bill extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'table_id',
        'branch_id',
        'user_id',
        'customer_id',
        'time_in',
        'time_out',
        'total',
        'discount',
        'final_total',
        'payment_method',
        'pay_status',
        'voucher_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'payment_method' => PaymentMethods::class,
        'pay_status' => PayStatus::class,
    ];

    //Model Relations
    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function billDetails()
    {
        return $this->hasMany(BillDetail::class);
    }
}
