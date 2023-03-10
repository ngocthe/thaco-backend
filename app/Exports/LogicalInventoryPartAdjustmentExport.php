<?php

namespace App\Exports;


use App\Models\LogicalInventoryPartAdjustment;
use App\Services\LogicalInventoryPartAdjustmentService;
use Carbon\Carbon;

class LogicalInventoryPartAdjustmentExport extends BaseExport
{
    const TITLE = 'Warehouse Adjustment (Logical Inventory - Part)';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 17;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new LogicalInventoryPartAdjustmentService(), self::TITLE, $fileTitle);
    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'Part No.',
            'Part Color Code',
            'Old Quantity',
            'New Quantity',
            'Adjustment Quantity',
            'Adjustment Date',
            'Plant Code'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param LogicalInventoryPartAdjustment $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            $row->part_code,
            $row->part_color_code,
            $row->old_quantity,
            $row->new_quantity,
            $row->adjustment_quantity,
            Carbon::createFromFormat('Y-m-d', $row->adjustment_date)->format('d/m/Y'),
            $row->plant_code
        ], $this->type == 'xls');
    }
}
