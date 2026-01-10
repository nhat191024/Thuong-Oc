<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CustomerMenuController;
use App\Http\Controllers\OrderController;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\VoucherController;

Route::get('/menu/{tableId}', [CustomerMenuController::class, 'index'])->name('customer-menu.index');
Route::post('/order', [OrderController::class, 'placeOrder'])->name('order.place');

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'storeRegister'])->name('register.store');

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('staff')->name('staff.')->middleware('role:staff')->group(function () {
        Route::get('/', [StaffController::class, 'index'])->name('tables');
        Route::get('/table/{tableId}', [StaffController::class, 'showTable'])->name('table.show');
        Route::get('/table/{tableId}/bill', [StaffController::class, 'showBill'])->name('table.bill');
        Route::post('/table/{tableId}/apply-discount', [StaffController::class, 'applyDiscount'])->name('table.apply-discount');
        Route::post('/table/{tableId}/remove-discount', [StaffController::class, 'removeDiscount'])->name('table.remove-discount');
        Route::post('/table/{tableId}/attach-customer', [StaffController::class, 'attachCustomer'])->name('table.attach-customer');
        Route::post('/table/{tableId}/remove-customer', [StaffController::class, 'removeCustomer'])->name('table.remove-customer');
        Route::post('/table/{tableId}/pay', [StaffController::class, 'processPayment'])->name('table.pay');
        Route::post('/table/{tableId}/move', [StaffController::class, 'moveTable'])->name('table.move');
    });

    Route::prefix('kitchen')->name('kitchen.')->middleware('role:kitchen')->group(function () {
        Route::get('/', [KitchenController::class, 'index'])->name('index');
        Route::get('/{kitchen}', [KitchenController::class, 'show'])->name('show');
        Route::get('/{kitchen}/history', [KitchenController::class, 'history'])->name('history');
        Route::post('/bill-detail/{billDetail}/status', [KitchenController::class, 'updateStatus'])->name('bill-detail.update-status');
    });

    Route::get('/payment/result', [OrderController::class, 'paymentResult'])->name('payment.result');

    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    Route::get('/history/detail/{id}', [HistoryController::class, 'show'])->name('history.show');

    Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
});
