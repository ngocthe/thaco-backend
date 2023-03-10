<?php

namespace App\Exports;

use App\Models\Part;
use App\Services\PartService;

class PartExport extends BaseExport
{
    const TITLE = 'Part List';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 13;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new PartService, self::TITLE, $fileTitle);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'Part No.',
            'Part Name',
            'Part Group',
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
     * @param Part $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            $row->code,
            $row->name,
            $row->group,
            $row->ecn_in,
            $row->ecn_in && $row->ecnInInfo->actual_line_off_date ? $row->ecnInInfo->actual_line_off_date->format('d/m/Y') : null,
            $row->ecn_out,
            $row->ecn_out && $row->ecnOutInfo->actual_line_off_date ? $row->ecnOutInfo->actual_line_off_date->format('d/m/Y') : null,
            $row->plant_code
        ], $this->type == 'xls');
    }
}
