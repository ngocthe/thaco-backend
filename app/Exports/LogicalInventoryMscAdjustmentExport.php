<?php

namespace App\Exports;


use App\Models\LogicalInventoryMscAdjustment;
use App\Services\LogicalInventoryMscAdjustmentService;
use Carbon\Carbon;

class LogicalInventoryMscAdjustmentExport extends BaseExport
{
    const TITLE = 'Warehouse Adjustment (Logical Inventory - MSC)';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 17;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new LogicalInventoryMscAdjustmentService, self::TITLE, $fileTitle);
    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'MSC Code',
            'Exterior Color Code',
            'Adjustment Quantity',
            'Production Date',
            'Plant Code'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param LogicalInventoryMscAdjustment $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            $row->msc_code,
            $row->vehicle_color_code,
            $row->adjustment_quantity,
            Carbon::createFromFormat('Y-m-d', $row->production_date)->format('d/m/Y'),
            $row->plant_code
        ], $this->type == 'xls');
    }
}
