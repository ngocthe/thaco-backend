<?php

namespace App\Exports;

use App\Models\VehicleColor;
use App\Services\VehicleColorService;

class VehicleColorExport extends BaseExport
{
    const TITLE = 'Vehicle Color List';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 18;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new VehicleColorService, self::TITLE, $fileTitle);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'Vehicle Color Code',
            'Vehicle Color Type',
            'Vehicle Color Name',
            'ECN No. In',
            'ECN No. Out',
            'Plant Code'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param VehicleColor $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            $row->code,
            $row->type,
            $row->name,
            $row->ecn_in,
            $row->ecn_out,
            $row->plant_code
        ], $this->type == 'xls');
    }
}
