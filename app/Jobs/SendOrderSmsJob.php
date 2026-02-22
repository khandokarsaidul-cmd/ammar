<?php

namespace App\Jobs;

use App\Models\Order;
use App\Traits\SmsTrait; // or your custom SMS method
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOrderSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SmsTrait;

    /**
     * Maximum retry attempts.
     */
    public $tries = 3;

    /**
     * Timeout for each attempt.
     */
    public $timeout = 30;

    protected int $orderId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $order = Order::find($this->orderId);

            if (!$order) {
                Log::warning('⚠️ SendOrderSmsJob: Order not found', [
                    'orderId' => $this->orderId,
                ]);
                return;
            }

            // Replace this with your SMS sending function
            $this->sendOrderSms($order->id);

            Log::info('📩 Order SMS sent successfully', [
                'orderId' => $order->id,
                'customer' => $order->customer_id,
            ]);
        } catch (\Throwable $e) {
            Log::error('❌ SMS sending failed', [
                'orderId' => $this->orderId,
                'error' => $e->getMessage(),
            ]);

            // Retry after 10 seconds if it fails (e.g., network issue)
            $this->release(10);
        }
    }
}
