<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TemplateExport extends BaseExport
{
    use Exportable;

    /**
     * @var array
     */
    protected array $headingsClass;
    protected array $dataTesting;

    public function __construct($headingsClass, $title = '', $dataTesting = [])
    {
        $this->headingsClass = $headingsClass;
        $this->dataTesting = $dataTesting;
        parent::__construct(null, $title);
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return new Collection([]);
    }

    public function headings(): array
    {
        return array_values($this->headingsClass);
    }

    /**
     * Mapping data
     *
     * @param $row
     * @return array
     */
    public function map($row): array
    {
        return [];
    }

    public function styles(Worksheet $sheet): array
    {
        foreach ($this->dataTesting as $index => $data) {
            $sheet->fromArray($data, null, 'A' . ($index + 4), false, false);
        }
        return parent::styles($sheet);
    }
}
