<?php

namespace App\Exports;

use App\Models\Bom;
use App\Services\BomService;

class BomExport extends BaseExport
{
    const TITLE = 'BOM List';

    public function __construct($fileTitle = '')
    {
        parent::__construct(new BomService, self::TITLE, $fileTitle);
    }

    public function headings(): array
    {
        return $this->addNoTitle([
            'MSC',
            'Shop Code',
            'Part No.',
            'Part Color Code',
            'Quantity per Unit',
            'Part Remarks',
            'ECN No. In',
            'ECN No. Out',
            'Plant Code'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param Bom $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            $row->msc_code,
            $row->shop_code,
            $row->part_code,
            $row->part_color_code,
            $row->quantity,
            $row->part_remarks,
            $row->ecn_in,
            $row->ecn_out,
            $row->plant_code
        ], $this->type == 'xls');
    }
}
