<?php

use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;


Route::post('/orders', [OrderController::class, 'store']);
Route::get('/customers/orders', [OrderController::class, 'customerOrders']);
Route::get('/products/low_stock', [OrderController::class, 'lowStockProducts']);
Route::get('/products', [OrderController::class, 'products']);