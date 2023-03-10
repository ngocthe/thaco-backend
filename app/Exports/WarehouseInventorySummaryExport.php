<?php

namespace App\Exports;

use App\Models\WarehouseInventorySummary;
use App\Services\WarehouseInventorySummaryService;

class WarehouseInventorySummaryExport extends BaseExport
{
    const TITLE = 'WH Inventory Summary (Warehouse)';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 16;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new WarehouseInventorySummaryService, self::TITLE, $fileTitle);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'Warehouse Code',
            'Part No.',
            'Part Color Code',
            'Quantity',
            'Unit of Measure',
            'Plant Code'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param WarehouseInventorySummary $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            $row->warehouse_code,
            $row->part_code,
            $row->part_color_code,
            $row->quantity,
            $row->unit,
            $row->plant_code
        ], $this->type == 'xls');
    }
}
