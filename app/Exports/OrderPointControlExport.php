<?php

namespace App\Exports;


use App\Models\OrderPointControl;
use App\Services\OrderPointControlService;

class OrderPointControlExport extends BaseExport
{
    const TITLE = 'Ordering Point Control';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 12;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new OrderPointControlService, self::TITLE, $fileTitle);
    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'Part No.',
            'Part Color Code',
            'Standard Stock of Part Box',
            'Ordering Lot Number of Boxes',
            'Box Type Code',
            'Plant Code'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param OrderPointControl $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            $row->part_code,
            $row->part_color_code,
            $row->standard_stock,
            $row->ordering_lot,
            $row->box_type_code,
            $row->plant_code
        ], $this->type == 'xls');
    }
}
