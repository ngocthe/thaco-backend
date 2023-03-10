<?php

namespace App\Exports;

use App\Models\PartColor;
use App\Services\PartColorService;

class PartColorExport extends BaseExport
{
    const TITLE = 'Part Color List';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 14;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new PartColorService, self::TITLE, $fileTitle);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'Part No.',
            'Part Color Code',
            'Part Color Name',
            'Interior Color Condition',
            'Exterior Color Condition',
            'ECN No. In',
            'ECN No. In Date',
            'ECN No. Out',
            'ECN No. Out Date',
            'Plant Code'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param PartColor $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            $row->part_code,
            $row->code,
            $row->name,
            $row->interior_code,
            $row->vehicle_color_code,
            $row->ecn_in,
            $row->ecn_in && $row->ecnInInfo->actual_line_off_date ? $row->ecnInInfo->actual_line_off_date->format('d/m/Y') : null,
            $row->ecn_out,
            $row->ecn_out && $row->ecnOutInfo->actual_line_off_date ? $row->ecnOutInfo->actual_line_off_date->format('d/m/Y') : null,
            $row->plant_code
        ], $this->type == 'xls');
    }
}
