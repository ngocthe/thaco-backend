<?php

namespace App\Exports;

use App\Models\Supplier;
use App\Services\SupplierService;

class SupplierExport extends BaseExport
{
    const TITLE = 'Supplier List';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 30;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new SupplierService, self::TITLE, $fileTitle);
    }

    public function columnWidths(): array
    {
        if ($this->type == 'pdf') {
            return [];
        } else {
            return [
                'A' => $this->firstColumnWidth,
                'B' => 60,
                'C' => 90,
            ];
        }
    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'Procurement Supplier Code',
            'Procurement Supplier Code Description',
            'Address of Procurement Supplier',
            'Phone No. of Procurement Supplier',
            'Number of forecast by week',
            'Number of forecast by month',
            'Receiver',
            'BCC',
            'CC'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param Supplier $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            $row->code,
            $row->description,
            $row->address,
            $row->phone,
            $row->forecast_by_week,
            $row->forecast_by_month,
            $row->receiver ? implode(', ', json_decode($row->receiver, true)) : '',
            $row->bcc ? implode(', ', json_decode($row->bcc, true)) : '',
            $row->cc ? implode(', ', json_decode($row->cc, true)) : ''
        ], $this->type == 'xls');
    }
}
