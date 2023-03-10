<?php

namespace App\Exports;

use App\Models\BwhInventoryLog;
use App\Services\BwhInventoryLogService;

class BwhInventoryLogForUpkwhExport  extends BaseExport
{
    const TITLE = 'Unpack Warehouse Inventory Log';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 14;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new BwhInventoryLogService, self::TITLE, $fileTitle);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
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
            'Location Code',
            'Number of Boxes shipped to Assembly Plant',
            'Date Box shipped to Assembly Plant',
            'Plant Code'
        ];
    }

    /**
     * Mapping data
     *
     * @param BwhInventoryLog $row
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
            '',
            '',
            '',
            '',
            '',
            $row->plant_code
        ], true);
    }
}
