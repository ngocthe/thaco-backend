<?php

namespace App\Jobs;

use App\Models\BwhInventoryLog;
use App\Models\BwhOrderRequest;
use App\Services\BwhOrderRequestService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CalculateBwhOrderRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(BwhOrderRequestService $bwhOrderRequestService)
    {
        DB::beginTransaction();
        try {
            Log::alert('Start run calculate bwh order request.');
            $this->deleteOldBwhOrderRequestHasDefect();
            $bwhOrderRequestService->runBathCreateBwhOrderRequest();
            Log::alert('Run CalculateBwhOrderRequest Success!');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::emergency($exception);
        }
        DB::commit();

    }

    /**
     * @return void
     */
    private function deleteOldBwhOrderRequestHasDefect()
    {
        Log::alert('Delete old bwh order request has defect');
        $rows = BwhOrderRequest::query()
            ->select('bwh_order_requests.id', 'bwh_inventory_logs.id as bwh_id')
            ->join('bwh_inventory_logs', function($join) {
                $join->on('bwh_order_requests.contract_code', '=', 'bwh_inventory_logs.contract_code')
                    ->on('bwh_order_requests.invoice_code', '=', 'bwh_inventory_logs.invoice_code')
                    ->on('bwh_order_requests.bill_of_lading_code', '=', 'bwh_inventory_logs.bill_of_lading_code')
                    ->on('bwh_order_requests.container_code', '=', 'bwh_inventory_logs.container_code')
                    ->on('bwh_order_requests.case_code', '=', 'bwh_inventory_logs.case_code')
                    ->on('bwh_order_requests.plant_code', '=', 'bwh_inventory_logs.plant_code');
            })
            ->whereNotNull('defect_id')
            ->get()
            ->toArray();
        $bwhOrderRequestIds = [];
        $bwhIds = [];
        foreach ($rows as $row) {
            $bwhOrderRequestIds[] = $row['id'];
            $bwhIds[] = $row['bwh_id'];
        }

        if (count($bwhOrderRequestIds)) {
            Log::alert('Update requested = false of BwhInventoryLog have data: ', $bwhIds);
            BwhInventoryLog::query()
                ->whereIn('id', array_unique($bwhIds))
                ->update(['requested' => false]);

            Log::alert('Delete old bwh order request.');
            BwhOrderRequest::query()
                ->whereIn('id', array_unique($bwhOrderRequestIds))
                ->delete();
        }
    }

}
