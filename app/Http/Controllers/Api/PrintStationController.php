<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PrintStationKitchenResource;
use App\Http\Resources\PrinterResource;
use App\Models\Branch;
use App\Models\Kitchen;
use App\Models\Printer;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PrintStationController extends Controller
{
    public function index(int $branchId): AnonymousResourceCollection
    {
        Branch::query()->findOrFail($branchId);

        $printers = Printer::query()
            ->whereBranchId($branchId)
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        return PrinterResource::collection($printers)->additional([
            'meta' => [
                'branch_id' => $branchId,
                'channel' => "print-stations.{$branchId}",
                'pusher_channel' => "private-print-stations.{$branchId}",
                'event' => 'print.job.requested',
                'auth_endpoint' => '/api/broadcasting/auth',
            ],
        ]);
    }

    public function kitchens(int $branchId): AnonymousResourceCollection
    {
        Branch::query()->findOrFail($branchId);

        $kitchens = Kitchen::query()
            ->whereBranchId($branchId)
            ->orderBy('name')
            ->get();

        return PrintStationKitchenResource::collection($kitchens)->additional([
            'meta' => [
                'branch_id' => $branchId,
                'event' => 'print.job.requested',
                'auth_endpoint' => '/api/broadcasting/auth',
                'legacy_channel' => "print-stations.{$branchId}",
                'legacy_pusher_channel' => "private-print-stations.{$branchId}",
            ],
        ]);
    }
}
