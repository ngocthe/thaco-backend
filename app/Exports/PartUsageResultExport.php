<?php

namespace App\Exports;

use App\Models\PartUsageResult;
use App\Services\PartUsageResultService;
use Carbon\Carbon;

class PartUsageResultExport extends BaseExport
{
    const TITLE = 'Parts Usage Result';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 17;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new PartUsageResultService, self::TITLE, $fileTitle);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'Parts Used Date',
            'Part No.',
            'Part Color Code',
            'Parts Used Quantity',
            'Plant Code'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param PartUsageResult $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            Carbon::createFromFormat('Y-m-d', $row->used_date)->format('d/m/Y'),
            $row->part_code,
            $row->part_color_code,
            $row->quantity,
            $row->plant_code
        ], $this->type == 'xls');
    }
}
