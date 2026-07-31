<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PrinterResource;
use App\Models\Branch;
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
}
