<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::view('/orders/create', 'orders.create')
    ->name('orders.create');