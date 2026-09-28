<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;

// Web routes for the Order System
Route::view('/orders/create', 'orders.create')
    ->name('orders.create');