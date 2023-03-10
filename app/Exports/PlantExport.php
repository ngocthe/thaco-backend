<?php

namespace App\Exports;

use App\Models\Plant;
use App\Services\PlantService;

class PlantExport extends BaseExport
{
    const TITLE = 'Plant Code List';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 14;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new PlantService, self::TITLE, $fileTitle);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'Plant Code',
            'Plant Code Description'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param Plant $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            $row->code,
            $row->description,
        ], $this->type == 'xls');
    }
}
