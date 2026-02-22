<?php

namespace App\Jobs;

use App\CPU\OrderManager;
use App\Jobs\SendOrderSmsJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 60;

    protected array $orderDataList;
    protected string $uniqueID;

    /**
     * Create a new job instance.
     *
     * @param array $orderDataList  Array of order data
     * @param string $uniqueID      Unique order group ID
     */
    public function __construct(array $orderDataList, string $uniqueID)
    {
        $this->orderDataList = $orderDataList;
        $this->uniqueID = $uniqueID;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::beginTransaction();

        try {
            $orderIds = [];

            foreach ($this->orderDataList as $data) {
                // Generate order (contains all logic: stock, amounts, etc.)
                $orderId = OrderManager::generate_order($data);
                $orderIds[] = $orderId;

                // Queue SMS notification job for this order
                SendOrderSmsJob::dispatch($orderId)->onQueue('notifications');
            }

            DB::commit();

            Log::info('✅ ProcessOrderJob: Orders created successfully', [
                'uniqueID' => $this->uniqueID,
                'orders' => $orderIds,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('❌ ProcessOrderJob failed', [
                'uniqueID' => $this->uniqueID,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Automatically retry after 10 seconds
            $this->release(10);
        }
    }
}
