<?php

use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

// Api routes for the Order System
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/customers/orders', [OrderController::class, 'customerOrders']);

// Api routes for the Product System
Route::get('/products/low_stock', [OrderController::class, 'lowStockProducts']);
Route::get('/products', [OrderController::class, 'products']);