<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('kitchens.{branchId}', function ($user, $branchId) {
    return (int) $user->branch_id === (int) $branchId;
});
