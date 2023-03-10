<?php

namespace App\Exports;

use App\Models\BwhInventoryLog;
use App\Services\BwhInventoryLogService;

class BwhInventoryLogExport  extends BaseExport
{
    const TITLE = 'BWH Inventory Log';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 14;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new BwhInventoryLogService, self::TITLE, $fileTitle);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'Contract No.',
            'Invoice No.',
            'B/L No.',
            'Container No.',
            'Case No.',
            'Procurement Supplier Code',
            'Date Container Received',
            'Date Container Devanned',
            'Date Case Stored',
            'Warehouse Code',
            'Location Code',
            'Date Case shipped to UPKWH',
            'Plant Code',
            'Defect Status'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param BwhInventoryLog $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            $row->contract_code,
            $row->invoice_code,
            $row->bill_of_lading_code,
            $row->container_code,
            $row->case_code,
            $row->supplier_code,
            $row->container_received ? $row->container_received->format('d/m/Y') : '',
            $row->devanned_date ? $row->devanned_date->format('d/m/Y') : '',
            $row->stored_date ? $row->stored_date->format('d/m/Y') : '',
            $row->warehouse_code,
            $row->warehouse_location_code,
            $row->shipped_date ? $row->shipped_date->format('d/m/Y') : '',
            $row->plant_code,
            $this->getDefectLabel($row->defect_id)
        ], $this->type == 'xls');
    }
}
