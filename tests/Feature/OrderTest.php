<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_can_be_created_successfully(): void
    {
        $product = Product::factory()->create([
            'price_per_unit' => 100,
            'tax_percentage' => 10,
            'stock_on_hand' => 10,
        ]);

        $customer = Customer::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson('/api/orders', [
            'customer_email' => $customer->email,
            'customer_name' => $customer->name,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Order created successfully',
        ]);

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'subtotal' => 200,
            'tax' => 20,
            'grand_total' => 220,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_on_hand' => 8,
        ]);
    }

    public function test_order_fails_when_stock_is_insufficient(): void
{
    $product = Product::factory()->create([
        'price_per_unit' => 100,
        'tax_percentage' => 10,
        'stock_on_hand' => 2,
    ]);

    $customer = Customer::factory()->create([
        'email' => 'stock-test@example.com',
    ]);

    $response = $this->postJson('/api/orders', [
        'customer_email' => $customer->email,
        'customer_name' => $customer->name,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 5,
            ],
        ],
    ]);

    $response->assertStatus(422);

    $response->assertJson([
        'message' => "Insufficient stock for product: {$product->name}",
    ]);

    $this->assertDatabaseMissing('orders', [
        'customer_id' => $customer->id,
    ]);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'stock_on_hand' => 2,
    ]);
}
}