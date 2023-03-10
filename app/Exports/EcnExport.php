<?php

namespace App\Exports;

use App\Models\Ecn;
use App\Services\EcnService;

class EcnExport extends BaseExport
{
    const TITLE = 'ECN List';

    public function __construct($fileTitle = '')
    {
        parent::__construct(new EcnService, self::TITLE, $fileTitle);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'ECN No.',
            'ECN Page Number',
            'ECN Line Number',
            'ECN Description',
            'Mandatory Level',
            'Production Interchangeability',
            'Service Interchangeability',
            'ECN Released Party',
            'ECN Released Date',
            'Planned Inspection Line Off Effective Date',
            'Actual Inspection Line Off Effective Date',
            'Planned Packing Effective Date',
            'Actual Packing Effective Date',
            'First Implementation VIN',
            'First Implementation CKD Contract No.',
            'Plant Code'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param Ecn $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            $row->code,
            $row->page_number,
            $row->line_number,
            $row->description,
            $row->mandatory_level,
            $row->production_interchangeability,
            $row->service_interchangeability,
            $row->released_party,
            $row->released_date ? $row->released_date->format('d/m/Y') : null,
            $row->planned_line_off_date ? $row->planned_line_off_date->format('d/m/Y') : null,
            $row->actual_line_off_date ? $row->actual_line_off_date->format('d/m/Y') : null,
            $row->planned_packing_date ? $row->planned_packing_date->format('d/m/Y') : null,
            $row->actual_packing_date ? $row->actual_packing_date->format('d/m/Y') : null,
            $row->vin,
            $row->complete_knockdown,
            $row->plant_code
        ], $this->type == 'xls');
    }
}
