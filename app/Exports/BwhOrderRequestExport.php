<?php

namespace App\Exports;

use App\Models\BwhOrderRequest;
use App\Services\BwhOrderRequestService;

class BwhOrderRequestExport extends BaseExport
{
    const TITLE = 'BWH Order Request';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 14;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new BwhOrderRequestService, self::TITLE, $fileTitle);
    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'Order Number',
            'Contract No.',
            'Invoice No.',
            'B/L No.',
            'Case No.',
            'Order Triggered Part No.',
            'Part Color Code',
            'Quantity of Box',
            'Location Code',
            'Warehouse Code',
            'Plant Code'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param BwhOrderRequest $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            $row->order_number,
            $row->contract_code,
            $row->invoice_code,
            $row->bill_of_lading_code,
            $row->case_code,
            $row->part_code,
            $row->part_color_code,
            $row->box_quantity,
            $row->warehouse_location_code,
            $row->warehouse_code,
            $row->plant_code
        ], $this->type == 'xls');
    }
}
