<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //

    use HasFactory;

    protected $fillable = ['name', 'code', 'price_per_unit', 'tax_percentage', 'stock_on_hand'];

    // relationship with order items
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
