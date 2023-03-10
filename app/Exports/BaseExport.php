<?php

namespace App\Exports;

use App\Services\BaseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class BaseExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping,
    WithStyles, WithCustomValueBinder, ShouldAutoSize, WithCustomStartCell, WithEvents, WithColumnWidths,
    WithStrictNullComparison,WithProperties
{
    /**
     * @var int
     */
    protected int $startNum = 0;

    /**
     * @var int
     */
    protected int $totalRow = 0;

    /**
     * @var int
     */
    protected int $totalCell = 0;

    /**
     * @var int
     */
    protected int $firstColumnWidth = 10;

    /**
     * @var string
     */
    protected string $type = 'xls';

    /**
     * @var string
     */
    protected string $title = '';

    /**
     * @var BaseService
     */
    protected $modelService;

    /**
     * @var string
     */
    public string $fileTitle = '';


    public function __construct($service = null, $title = '', $fileTitle = '')
    {
        $this->type = request()->get('type', 'xls');
        $this->title = $title;
        $this->modelService = $service;
        $this->fileTitle = $fileTitle;
    }

    public function properties(): array
    {
        return [
            'title' => $this->title
        ];
    }

    public function columnWidths(): array
    {
        if ($this->type == 'pdf') {
            return [];
        } else {
            return [
                'A' => $this->firstColumnWidth,
            ];
        }
    }

    public function startCell(): string
    {
        return 'A3';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->setCellValue('A1', $this->title)
                    ->getStyle('A1')->getFont()->setSize(14);

                if ($this->type === 'pdf') {
                    $event->sheet->getDelegate()->mergeCells('A1:G1');
                    $event->sheet->setCellValue('A2', ' ');
                }

                $event->sheet->getStyle('A1:I2')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_NONE
                        ],
                    ],
                ]);

                $alphabetRange = range('A', 'Z');
                $this->totalCell = count($this->headings()) - 1;
                $alphabet = $alphabetRange[$this->totalCell];
                $cellRange      = 'A3:'.$alphabet.($this->totalRow + 3);

                $event->sheet->getStyle($cellRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN
                        ],
                    ],
                    'alignment' => ['wrapText' => true]
                ]);

                foreach($event->sheet->getRowDimensions() as $rd) {
                    $rd->setRowHeight(-1);
                }
            }
        ];
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
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [];
    }

    /**
     * @param $row
     * @return array
     */
    public function map($row): array
    {
        return [];
    }

    /**
     * @return LengthAwarePaginator|Builder[]|Collection
     */
    public function collection()
    {
        if ($this->type == 'xls') {
            $this->modelService->buildBasicQuery();
            $collection = $this->modelService->query->latest('id')->get();
            $this->totalRow = $collection->count();
        } else {
            $collection = $this->modelService->paginate();
            $this->totalRow = count($collection);
        }

        return $collection;
    }

    /**
     * @param array $headings
     * @param string $type
     * @return array
     */
    protected function addNoTitle(array $headings, string $type): array
    {
        if ($type != 'xls') {
            array_unshift($headings, 'No.');
        }
        return $headings;
    }

    /**
     * @param $data
     * @param bool $withoutNo
     * @return mixed
     */
    protected function transform($data, bool $withoutNo = false)
    {
        if ($withoutNo === false) {
            $this->startNum += 1;
            array_unshift($data, $this->startNum);
        }
        return $data;
    }

    /**
     * @param Cell $cell
     * @param $value
     * @return bool
     * @throws Exception
     */
    public function bindValue(Cell $cell, $value): bool
    {
        if (is_numeric($value)) {
            if ($value == 0) {
                $value = '';
            } else {
                $cell->setValueExplicit($value, DataType::TYPE_STRING);
                return true;
            }
        }
        // else return default behavior
        return parent::bindValue($cell, $value);
    }

    /**
     * @param string|null $defect_id
     * @return string
     */
    public function getDefectLabel(string $defect_id = null): string
    {
        $defect_id = $defect_id ? strtolower($defect_id) : 'o';
        $defectOptions = [
            'o' => 'OK',
            'w' => 'Wrong',
            'd' => 'Damage',
            'x' => 'No good',
            's' => 'Shortage'
        ];
        return $defectOptions[$defect_id] ?? 'OK';
    }
}
