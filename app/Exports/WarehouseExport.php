<?php

namespace App\Exports;

use App\Models\Warehouse;
use App\Services\WarehouseService;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WarehouseExport extends BaseExport
{
    const TITLE = 'WH Code List';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 18;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new WarehouseService, self::TITLE, $fileTitle);
    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'Warehouse Code',
            'Warehouse Description',
            'Plant Code'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param Warehouse $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            $row->code,
            $row->description,
            $row->plant_code
        ], $this->type == 'xls');
    }

    /**
     * @param Worksheet $sheet
     * @return bool[][][]
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
            'C' => ['font' => ['bold' => true]],
        ];
    }
}
