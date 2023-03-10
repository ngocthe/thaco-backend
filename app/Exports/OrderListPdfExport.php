<?php

namespace App\Exports;

use App\Constants\MRP;
use App\Models\OrderList;
use App\Services\OrderListService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithProperties;

class OrderListPdfExport implements FromView, WithProperties
{
    public function properties(): array
    {
        return [
            'title' => 'Order List'
        ];
    }

    public function view(): View
    {
        $orderListService = new OrderListService();
        $orderListService->buildQueryImportFile();
        $orderListService->buildQueryOrderList();
        $orderListService->buildBasicQuery(request()->except(['import_id']));

        $perPage = request()->get('per_page') ?? 20;

        $orderLists = $orderListService->query->latest('id')->paginate($perPage);

        return view('exports.order-list', [
            'orderLists' => $orderLists,
            'firstOrderList' => $orderLists[0] ?? new OrderList()
        ]);
    }
}
