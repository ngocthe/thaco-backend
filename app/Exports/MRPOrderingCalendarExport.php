<?php

namespace App\Exports;

use App\Constants\MRP;
use App\Models\BwhInventoryLog;
use App\Models\MrpOrderCalendar;
use App\Services\BwhInventoryLogService;
use App\Services\MrpOrderCalendarService;

class MRPOrderingCalendarExport extends BaseExport
{
    const TITLE = 'MRP Ordering Calendar';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 18;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new MrpOrderCalendarService(), self::TITLE, $fileTitle);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'Contract No.',
            'Part Group',
            'Status',
            'MRP/OR Run At',
            'Supplier Order Span From',
            'Supplier Order Span To',
            'ETD',
            'ETA',
            'Target Plan From',
            'Target Plan To',
            'Buffer Span From',
            'Buffer Span To',
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param MrpOrderCalendar $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            $row->contract_code,
            $row->part_group,
            $row->status ? MRP::getTextStatusMRPOrderCalendar($row->status) : '',
            $row->mrp_or_run ? $row->mrp_or_run->format('d/m/Y') : '',
            $row->order_span_from,
            $row->order_span_to,
            $row->etd ? $row->etd->format('d/m/Y') : '',
            $row->eta ? $row->eta->format('d/m/Y') : '',
            $row->target_plan_from,
            $row->target_plan_to,
            $row->buffer_span_from,
            $row->buffer_span_to,
        ], $this->type == 'xls');
    }
}
