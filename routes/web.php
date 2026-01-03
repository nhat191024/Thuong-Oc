<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CustomerMenuController;
use App\Http\Controllers\OrderController;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\KitchenController;

Route::get('/menu/{tableId}', [CustomerMenuController::class, 'index'])->name('customer-menu.index');
Route::post('/order', [OrderController::class, 'placeOrder'])->name('order.place');

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/', [StaffController::class, 'index'])->name('tables');
        Route::get('/table/{tableId}', [StaffController::class, 'showTable'])->name('table.show');
        Route::get('/table/{tableId}/bill', [StaffController::class, 'showBill'])->name('table.bill');
        Route::post('/table/{tableId}/pay', [StaffController::class, 'processPayment'])->name('table.pay');
    });

    Route::prefix('kitchen')->name('kitchen.')->group(function () {
        Route::get('/', [KitchenController::class, 'index'])->name('index');
    });

    Route::get('/payment/result', [OrderController::class, 'paymentResult'])->name('payment.result');
});
