<?php

namespace App\Exports;

use App\Models\Procurement;
use App\Services\ProcurementService;

class ProcurementExport extends BaseExport
{
    const TITLE = 'Procurement List';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 14;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new ProcurementService, self::TITLE, $fileTitle);
    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'Part No.',
            'Part Color Code',
            'Minimum Order Quantity',
            'Standard Number Of Boxes',
            'Parts Quantity In Box',
            'Unit of Measure',
            'Procurement Supplier Code',
            'Plant Code'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param Procurement $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            $row->part_code,
            $row->part_color_code,
            $row->minimum_order_quantity,
            $row->standard_box_quantity,
            $row->part_quantity,
            $row->unit,
            $row->supplier_code,
            $row->plant_code
        ], $this->type == 'xls');
    }
}
