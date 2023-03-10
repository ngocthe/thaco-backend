<?php

namespace App\Exports;

use App\Models\Msc;
use App\Services\MscService;

class MscExport extends BaseExport
{
    const TITLE = 'MSC List';

    public function __construct($fileTitle = '')
    {
        parent::__construct(new MscService, self::TITLE, $fileTitle);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'MSC',
            'MSC Description',
            'Interior Color Description',
            'Car-Line Name',
            'Model And Grade Name',
            'Body Description',
            'Engine Description',
            'TM Description',
            'Plant Code',
            'MSC Effective Date in',
            'MSC Effective Date out'
        ], $this->type);
    }

    /**
     * Mapping data
     *
     * @param Msc $row
     * @return array
     */
    public function map($row): array
    {
        return $this->transform([
            $row->code,
            $row->description,
            $row->interior_color,
            $row->car_line,
            $row->model_grade,
            $row->body,
            $row->engine,
            $row->transmission,
            $row->plant_code,
            $row->effective_date_in ? $row->effective_date_in->format('d/m/Y') : null,
            $row->effective_date_out ? $row->effective_date_out->format('d/m/Y') : null
        ], $this->type == 'xls');
    }
}
