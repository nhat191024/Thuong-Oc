<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\BillController;

Route::get('/ping', function () {
    return response()->json(['message' => 'pong'], 200);
});

include __DIR__ . '/api/auth.php';

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tables', [TableController::class, 'index']);

    // Branches
    Route::get('/branches', [BranchController::class, 'index']);
    Route::get('/branches/{id}', [BranchController::class, 'show']);

    // Bills & Payment
    Route::get('/tables/{table}/bill', [BillController::class, 'show']);
    Route::post('/tables/{table}/bill/customer', [BillController::class, 'attachCustomer']);
    Route::delete('/tables/{table}/bill/customer', [BillController::class, 'removeCustomer']);
    Route::post('/tables/{table}/bill/discount', [BillController::class, 'applyDiscount']);
    Route::delete('/tables/{table}/bill/discount', [BillController::class, 'removeDiscount']);
    Route::post('/tables/{table}/bill/pay', [BillController::class, 'processPayment']);
    Route::patch('/tables/{table}/bill/status', [BillController::class, 'updateStatus']);
});
