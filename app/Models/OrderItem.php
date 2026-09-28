<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    //

    protected $fillable = ['order_id', 'product_id', 'quantity', 'unit_price', 'tax_percentage'];

// relationship with order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
// relationship with product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
