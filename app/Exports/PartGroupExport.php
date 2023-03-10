<?php

namespace App\Exports;

use App\Models\PartGroup;
use App\Services\PartGroupService;
class PartGroupExport extends BaseExport
{
    const TITLE = 'Part Group List';

    /**
     * @var int
     */
    protected int $firstColumnWidth = 16;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new PartGroupService, self::TITLE, $fileTitle);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'Part Group Code',
            'Part Group Description',
            'Ordering Lead Time Week',
            'Ordering cycle',
            'Delivery Lead Time Date'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param PartGroup $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            $row->code,
            $row->description,
            $row->lead_time,
            $row->ordering_cycle,
            $row->delivery_lead_time
        ], $this->type == 'xls');
    }
}
