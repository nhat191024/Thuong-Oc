<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CustomerMenuController;
use App\Http\Controllers\OrderController;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StaffController;

Route::get('/menu/{tableId}', [CustomerMenuController::class, 'index'])->name('customer-menu.index');
Route::post('/order', [OrderController::class, 'placeOrder'])->name('order.place');

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/', [StaffController::class, 'index'])->name('tables');
        Route::get('/table/{tableId}', [StaffController::class, 'showTable'])->name('table.show');
    });
});
