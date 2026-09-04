<?php

namespace App\Services;

use App\Models\InventoryUnit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class InventoryUnitService
{
    public const CACHE_KEY = 'inventory-units';

    /** @return Collection<int, InventoryUnit> */
    public static function all(): Collection
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn (): Collection => InventoryUnit::query()->orderBy('name')->get()
        );
    }

    /** @return array<int, string> */
    public static function options(): array
    {
        return self::all()->mapWithKeys(
            fn (InventoryUnit $unit): array => [$unit->id => "{$unit->name} ({$unit->symbol})"]
        )->all();
    }

    public static function forgetCache(): bool
    {
        return Cache::forget(self::CACHE_KEY);
    }
}
