<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CustomerMenuController;
use App\Http\Controllers\OrderController;

use App\Http\Controllers\AuthController;

Route::get('/menu/{tableId}', [CustomerMenuController::class, 'index'])->name('customer-menu.index');
Route::post('/order', [OrderController::class, 'placeOrder'])->name('order.place');

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
