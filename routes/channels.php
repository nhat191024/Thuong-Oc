<?php

use App\Models\Branch;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('kitchens.{branchId}', function ($user, $branchId) {
    return (int) $user->branch_id === (int) $branchId;
});

Broadcast::channel('print-stations.{branchId}', function ($user, $branchId) {
    $branchId = (int) $branchId;
    $authorized = Branch::query()->whereKey($branchId)->exists();

    Log::info('Print station channel authorization', [
        'authorized' => $authorized,
        'user_id' => $user->id,
        'user_branch_id' => $user->branch_id,
        'requested_branch_id' => $branchId,
        'channel' => "private-print-stations.{$branchId}",
    ]);

    return $authorized;
});
