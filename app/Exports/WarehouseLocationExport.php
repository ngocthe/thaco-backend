<?php

namespace App\Exports;

use App\Models\WarehouseLocation;
use App\Services\WarehouseLocationService;
class WarehouseLocationExport extends BaseExport
{
    const TITLE = 'WH Location Code List';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 17;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new WarehouseLocationService, self::TITLE, $fileTitle);
    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'Warehouse Code',
            'Location Code',
            'Location Description',
            'Plant Code'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param WarehouseLocation $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            $row->warehouse_code,
            $row->code,
            $row->description,
            $row->plant_code
        ], $this->type == 'xls');
    }
}
