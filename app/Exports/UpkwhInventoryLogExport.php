<?php

namespace App\Exports;

use App\Models\UpkwhInventoryLog;
use App\Services\UpkwhInventoryLogService;

class UpkwhInventoryLogExport  extends BaseExport
{
    const TITLE = 'UPKWH Inventory Log';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 14;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new UpkwhInventoryLogService, self::TITLE, $fileTitle);
    }

    /**
     * @return array
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
            'Date Case Received',
            'Warehouse Code',
            'Shelf Location Code',
            'Quantity of Boxes',
            'Number of Boxes shipped to Assembly Plant',
            'Date Box shipped to Assembly Plant',
            'Plant Code',
            'Defect Status'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param UpkwhInventoryLog $row
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
            $row->received_date ? $row->received_date->format('d/m/Y') : '',
            $row->warehouse_code,
            $row->shelf_location_code,
            $row->box_quantity,
            $row->shipped_box_quantity,
            $row->shipped_date ? $row->shipped_date->format('d/m/Y') : '',
            $row->plant_code,
            $this->getDefectLabel($row->defect_id)
        ], $this->type == 'xls');
    }
}
