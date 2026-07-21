<?php

use Inertia\Inertia;

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CustomerMenuController;
use App\Http\Controllers\OrderController;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\VoucherController;

Route::get('/', function () {
    return Inertia::render('Landing');
})->name('home');

Route::get('/menu/{tableId}', [CustomerMenuController::class, 'index'])->name('customer-menu.index');
Route::post('/order', [OrderController::class, 'placeOrder'])->name('order.place');

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'storeRegister'])->name('register.store');

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('staff')->name('staff.')->middleware('role:staff|table-admin')->group(function () {
        Route::get('/', [StaffController::class, 'index'])->name('tables');
        Route::get('/stock', [StaffController::class, 'stockIndex'])->name('stock.index');
        Route::patch('/stock/{food}', [StaffController::class, 'updateStock'])->name('stock.update');
        Route::get('/table/{tableId}', [StaffController::class, 'showTable'])->name('table.show');
        Route::get('/table/{tableId}/bill', [StaffController::class, 'showBill'])->name('table.bill');
        Route::delete('/table/{tableId}/bill', [StaffController::class, 'destroyBill'])
            ->middleware('role:table-admin')
            ->name('table.bill.destroy');
        Route::patch('/table/{tableId}/bill-details/cancel', [StaffController::class, 'cancelBillDetails'])
            ->middleware('role:table-admin')
            ->name('table.bill-details.cancel');
        Route::patch('/table/{tableId}/bill-details/reduce', [StaffController::class, 'reduceBillDetailQuantity'])
            ->middleware('role:table-admin')
            ->name('table.bill-details.reduce');
        Route::post('/table/{tableId}/apply-discount', [StaffController::class, 'applyDiscount'])->name('table.apply-discount');
        Route::post('/table/{tableId}/remove-discount', [StaffController::class, 'removeDiscount'])->name('table.remove-discount');
        Route::post('/table/{tableId}/attach-customer', [StaffController::class, 'attachCustomer'])->name('table.attach-customer');
        Route::post('/table/{tableId}/remove-customer', [StaffController::class, 'removeCustomer'])->name('table.remove-customer');
        Route::post('/table/{tableId}/pay', [StaffController::class, 'processPayment'])->name('table.pay');
        Route::post('/table/{tableId}/move', [StaffController::class, 'moveTable'])->name('table.move');
        Route::post('/table/{tableId}/merge', [StaffController::class, 'mergeTable'])->name('table.merge');
    });

    Route::prefix('kitchen')->name('kitchen.')->middleware('role:kitchen')->group(function () {
        Route::get('/', [KitchenController::class, 'index'])->name('index');
        Route::get('/print-station', [KitchenController::class, 'printStation'])->name('print-station');
        Route::post('/print-station/bill-detail/{billDetail}', [KitchenController::class, 'printBillDetail'])->name('print-station.bill-detail.print');
        Route::patch('/{kitchen}/print-settings', [KitchenController::class, 'updatePrintSettings'])->name('print-settings.update');
        Route::get('/{kitchen}', [KitchenController::class, 'show'])->name('show');
        Route::get('/{kitchen}/history', [KitchenController::class, 'history'])->name('history');
        Route::post('/bill-detail/{billDetail}/status', [KitchenController::class, 'updateStatus'])
            ->missing(fn () => back()->with('error', 'Đơn đã bị xóa, không thể cập nhật món.'))
            ->name('bill-detail.update-status');
    });

    Route::get('/payment/result', [OrderController::class, 'paymentResult'])->name('payment.result');

    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    Route::get('/history/detail/{id}', [HistoryController::class, 'show'])->name('history.show');

    Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
});
