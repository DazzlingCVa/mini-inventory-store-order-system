<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmation implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order

    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        Log::info('Order confirmation sent', [
            'order_id' => $this->order->id,
            'customer_id' => $this->order->customer_id,
        ]);
    }
}
