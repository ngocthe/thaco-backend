<?php

namespace App\Exports;

use App\Models\BoxType;
use App\Services\BoxTypeService;

class BoxTypeExport extends BaseExport
{
    const TITLE = 'Box Type List';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 14;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new BoxTypeService, self::TITLE, $fileTitle);
    }

    public function headings(): array
    {
        return $this->addNoTitle([
            'Part No.',
            'Box Type Code',
            'Box Type Description',
            'Box Weight Gram',
            'Size of Box X mm',
            'Size of Box Y mm',
            'Size of Box Z mm',
            'Part Quantity in Box',
            'Unit of Measure',
            'Plant Code'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param BoxType $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            $row->part_code,
            $row->code,
            $row->description,
            $row->weight,
            $row->width,
            $row->height,
            $row->depth,
            $row->quantity,
            $row->unit,
            $row->plant_code
        ], $this->type == 'xls');
    }
}
