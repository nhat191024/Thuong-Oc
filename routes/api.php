<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\TableController;

include __DIR__ . '/api/auth.php';

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tables', [TableController::class, 'index']);
});
