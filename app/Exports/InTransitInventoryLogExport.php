<?php

namespace App\Exports;

use App\Models\InTransitInventoryLog;
use App\Services\InTransitInventoryLogService;

class InTransitInventoryLogExport extends BaseExport
{
    const TITLE = 'In-Transit Inventory Log';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 14;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new InTransitInventoryLogService, self::TITLE, $fileTitle);
    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'Contract No.',
            'Invoice No.',
            'B/L No.',
            'Container No.',
            'Case No.',
            'Part No.',
            'Part Color Code',
            'Box Type Code',
            'Quantity of Box',
            'Part Quantity in Box',
            'Unit of Measure',
            'Procurement Supplier Code',
            'ETD',
            'Date Container Shipped',
            'ETA',
            'Plant Code'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param InTransitInventoryLog $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            $row->contract_code,
            $row->invoice_code,
            $row->bill_of_lading_code,
            $row->container_code,
            $row->case_code,
            $row->part_code,
            $row->part_color_code,
            $row->box_type_code,
            $row->box_quantity,
            $row->part_quantity,
            $row->unit,
            $row->supplier_code,
            $row->etd ? $row->etd->format('d/m/Y') : '',
            $row->container_shipped ? $row->container_shipped->format('d/m/Y') : '',
            $row->eta ? $row->eta->format('d/m/Y') : '',
            $row->plant_code
        ], $this->type == 'xls');
    }
}
