<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableSalesSummary extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'summary_date',
        'branch_id',
        'table_id',
        'table_number',
        'table_name',
        'bill_count',
        'gross_revenue',
        'discount_amount',
        'net_revenue',
        'last_paid_at',
        'calculated_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'summary_date' => 'date',
            'last_paid_at' => 'datetime',
            'calculated_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
