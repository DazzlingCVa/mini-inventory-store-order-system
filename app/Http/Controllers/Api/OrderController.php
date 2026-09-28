<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Jobs\SendOrderConfirmation;
use App\Models\Product;


class OrderController extends Controller
{
    // Store a new order
    public function store(Request $request)
    {
        // we will validate the request data
        $validated = $request->validate([
            'customer_email' => 'required|email',
            'customer_name' => 'required|string|max:255',

            'items' => 'required|array|min:1',

            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        //Check if the customer exists, if not create a new customer
        $customer = Customer::firstOrCreate(
            ['email' => $validated['customer_email']],
            ['name' => $validated['customer_name']]
        );

        // Initialize Value for subtotal and tax
        $subtotal = 0;
        $tax = 0;
        $order = null;

        // We will use a database transaction to ensure that the order and its items are created atomically
        DB::transaction(function () use ($validated, $customer, &$subtotal, &$tax, &$order) {
            $products = [];
            foreach ($validated['items'] as $item) {

                $product = Product::where('id', $item['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();


                if ($product->stock_on_hand < $item['quantity']) {
                    throw new HttpResponseException(
                        response()->json([
                            'message' => "Insufficient stock for product: {$product->name}",
                        ], 422)
                    );
                }

                $lineSubtotal = $product->price_per_unit * $item['quantity'];

                $lineTax = $lineSubtotal * ($product->tax_percentage / 100);

                $subtotal += $lineSubtotal;
                $tax += $lineTax;

                $products[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                ];
            }

            $subtotal = round($subtotal, 2);
            $tax = round($tax, 2);
            // Order Will be Created
            $order = Order::create([
                'customer_id' => $customer->id,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'grand_total' => round($subtotal + $tax, 2),
            ]);

            foreach ($products as $items) {
                // Order item will be created
                $product = $items['product'];
                $quantity = $items['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price_per_unit,
                    'tax_percentage' => $product->tax_percentage,
                ]);

                // Deduct the Stock on hand for the product
                $product->decrement('stock_on_hand', $quantity);
            }
        });

        // Dispatch the job to send order confirmation email
        SendOrderConfirmation::dispatch($order);

        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order,
        ]);

    }

    // Get all orders for a specific customer by email
    public function customerOrders(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $customer = Customer::where('email', $validated['email'])->first();

        if (!$customer) {
            return response()->json([
                'message' => 'Customer not found',
            ], 404);
        }

        $orders = Order::where('customer_id', $customer->id)
            ->with('items.product')
            ->latest()
            ->get();

        return response()->json([
            'customer' => $customer,
            'orders' => $orders,
        ]);
    }

    // Get products with stock on hand less than or equal to a specified threshold
    public function lowStockProducts(Request $request)
    {
        $validated = $request->validate([
            'threshold' => 'required|integer|min:0',
        ]);

        $products = Product::where(
            'stock_on_hand',
            '<=',
            $validated['threshold']
        )->get();

        return response()->json([
            'threshold' => $validated['threshold'],
            'products' => $products,
        ]);
    }

    // Get all products ordered by name
    public function products()
    {
        $products = Product::orderBy('name')->get();

        return response()->json([
            'products' => $products,
        ]);
    }
}
