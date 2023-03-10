<?php

namespace App\Jobs;

use App\Models\LogicalInventory;
use App\Models\OrderList;
use App\Models\PartUsageResult;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateLogicalInventory implements ShouldQueue
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
    public function handle()
    {
        $yesterday = Carbon::now()->subDay()->toDateString();
        $productionDate = Carbon::now()->toDateString();

        $partQuantities = $this->getPartUsageResults($yesterday);
        $partQuantities = $this->getOrderListHaveETAToday($partQuantities, $productionDate);
        $partQuantities = $this->getLogicalInventoriesYesterday($partQuantities, $yesterday);
        DB::beginTransaction();
        try {
            $this->handleInsertLogicalInventory($partQuantities, $productionDate);
            Log::alert('Update logical inventory Success!');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::emergency($exception);
        }
        DB::commit();
    }

    /**
     * @param $yesterday
     * @return array
     */
    private function getPartUsageResults($yesterday): array
    {
        $partUsageResults = PartUsageResult::query()
            ->select([
                'part_code',
                'part_color_code',
                'plant_code',
                'quantity'
            ])
            ->where('used_date', $yesterday)
            ->get()
            ->toArray();
        $partQuantities = [];
        foreach ($partUsageResults as $partUsageResult) {
            $partCode = [$partUsageResult['part_code'], $partUsageResult['part_color_code'], $partUsageResult['plant_code']];
            $key = implode('|', $partCode);
            $partQuantities[$key] = -$partUsageResult['quantity'];
        }
        Log::alert('Part usage result yesterday: ', $partQuantities);
        return $partQuantities;
    }

    /**
     * @param $partQuantities
     * @param $today
     * @return array
     */
    private function getOrderListHaveETAToday($partQuantities, $today): array
    {
        $orderLists = OrderList::query()
            ->select([
                'part_code',
                'part_color_code',
                'plant_code',
                'actual_quantity'
            ])
            ->where('eta', $today)
            ->get()
            ->toArray();
        foreach ($orderLists as $order) {
            $partCode = [$order['part_code'], $order['part_color_code'], $order['plant_code']];
            $key = implode('|', $partCode);
            if (!isset($partQuantities[$key])) {
                $partQuantities[$key] = 0;
            }
            $partQuantities[$key] += $order['actual_quantity'];
        }
        Log::alert('Part in Order list have ETA is Today: ', $partQuantities);
        return $partQuantities;
    }

    /**
     * @param $partQuantities
     * @param $yesterday
     * @return mixed
     */
    private function getLogicalInventoriesYesterday($partQuantities, $yesterday)
    {
        $logicalInventories = LogicalInventory::query()
            ->select([
                'part_code',
                'part_color_code',
                'plant_code',
                'quantity'
            ])
            ->where('production_date', $yesterday)
            ->get()
            ->toArray();

        foreach ($logicalInventories as $logicalInventory) {
            $partCode = [$logicalInventory['part_code'], $logicalInventory['part_color_code'], $logicalInventory['plant_code']];
            $key = implode('|', $partCode);
            if (!isset($partQuantities[$key])) {
                $partQuantities[$key] = 0;
            }
            $partQuantities[$key] += $logicalInventory['quantity'];
        }
        Log::alert('Logical inventory yesterday: ', $partQuantities);
        return $partQuantities;
    }

    /**
     * @param $partQuantities
     * @param $productionDate
     * @return void
     */
    private function handleInsertLogicalInventory($partQuantities, $productionDate)
    {
        $logicalInventoriesInsert = [];
        foreach ($partQuantities as $partQuantity => $quantity) {
            $partQuantity = explode('|', $partQuantity);
            $logicalInventoriesInsert[] = [
                'production_date' => $productionDate,
                'part_code' => $partQuantity[0],
                'part_color_code' => $partQuantity[1],
                'quantity' => $quantity,
                'plant_code' => $partQuantity[2]
            ];
        }
        Log::alert('INSERT Logical inventory for today: ', $logicalInventoriesInsert);
        if (count($logicalInventoriesInsert)) {
            $logicalInventoriesInsert = array_chunk($logicalInventoriesInsert, 1000);
            foreach ($logicalInventoriesInsert as $data) {
                LogicalInventory::query()
                    ->upsert(
                        $data,
                        [
                            'production_date',
                            'part_code',
                            'part_color_code',
                            'plant_code'
                        ],
                        [
                            'quantity'
                        ]
                    );
            }
        }
    }
}
