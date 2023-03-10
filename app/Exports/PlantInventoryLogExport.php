<?php

namespace App\Exports;

use App\Models\PlantInventoryLog;
use App\Services\PlantInventoryLogService;

class PlantInventoryLogExport extends BaseExport
{
    const TITLE = 'Plant WH Inventory Log';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 12;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new PlantInventoryLogService, self::TITLE, $fileTitle);
    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'Part No.',
            'Part Color Code',
            'Date Box received at Assembly Plant',
            'Number of Boxes Received',
            'Box Type Code',
            'Parts Quantity',
            'Unit of Measure',
            'Warehouse Code',
            'Plant Code',
            'Defect Status'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param PlantInventoryLog $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            $row->part_code,
            $row->part_color_code,
            $row->received_date ? $row->received_date->format('d/m/Y') : '',
            $row->received_box_quantity,
            $row->box_type_code,
            $row->quantity,
            $row->unit,
            $row->warehouse_code,
            $row->plant_code,
            $row->defect_id ? 'Has Defect' : 'OK'
        ], $this->type == 'xls');
    }
}
