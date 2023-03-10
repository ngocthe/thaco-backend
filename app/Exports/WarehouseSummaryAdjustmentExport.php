<?php

namespace App\Exports;


use App\Models\WarehouseSummaryAdjustment;
use App\Services\WarehouseSummaryAdjustmentService;

class WarehouseSummaryAdjustmentExport extends BaseExport
{
    const TITLE = 'Warehouse Adjustment (Warehouse Summary)';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 17;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new WarehouseSummaryAdjustmentService(), self::TITLE, $fileTitle);
    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'Warehouse Code',
            'Part No.',
            'Part Color Code',
            'Old Quantity',
            'New Quantity',
            'Adjustment Quantity',
            'Plant Code'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param WarehouseSummaryAdjustment $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            $row->warehouse_code,
            $row->part_code,
            $row->part_color_code,
            $row->old_quantity ?: 0,
            $row->new_quantity ?: 0,
            $row->adjustment_quantity ?: 0,
            $row->plant_code
        ], $this->type == 'xls');
    }
}
