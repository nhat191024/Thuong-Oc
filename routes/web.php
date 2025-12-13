<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CustomerMenuController;
use App\Http\Controllers\OrderController;

Route::get('/menu/{tableId}', [CustomerMenuController::class, 'index'])->name('customer-menu.index');
Route::post('/order', [OrderController::class, 'placeOrder'])->name('order.place');
