<?php

namespace App\Exports;

use App\Services\OrderListService;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DeliveringListsExport implements WithMultipleSheets
{
    /**
     * @return array
     */
    public function sheets(): array
    {
        $orderListService = new OrderListService();
        $orderLists = $orderListService->getOrderListGroupByContract(true);
        $sheets = [];

        foreach ($orderLists as $orderList) {
            $sheets[] = new OrderListExport($orderList);
        }

        if (count($orderLists) === 0) {
            $sheets[] = new OrderListExport([
                [
                    'contract_code' => ' ',
                    'part_code' => null,
                    'part_color_code' => null,
                    'actual_quantity' => null,
                    'eta' => null,
                    'target_plan_to' => null,
                ]
            ]);
        }

        return $sheets;
    }

    public function properties(): array
    {
        return [
            'title' => 'Order List'
        ];
    }
}
