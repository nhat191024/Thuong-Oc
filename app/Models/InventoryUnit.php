<?php

namespace App\Models;

use App\Services\InventoryUnitService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryUnit extends Model
{
    protected $fillable = [
        'name',
        'symbol',
    ];

    protected static function booted(): void
    {
        static::saved(function (): void {
            InventoryUnitService::forgetCache();
        });
        static::deleted(function (): void {
            InventoryUnitService::forgetCache();
        });
    }

    public function foods(): HasMany
    {
        return $this->hasMany(Food::class);
    }
}
