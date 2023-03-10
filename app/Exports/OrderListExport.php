<?php

namespace App\Exports;

use App\Helpers\DateTimeHelper;
use App\Models\OrderList;
use App\Services\OrderListService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithPreCalculateFormulas;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrderListExport extends BaseExport implements WithPreCalculateFormulas, WithTitle
{
    const TITLE = 'Order No.';

    public function startCell(): string
    {
        return 'A9';
    }

    public array $orderList = [];

    public function __construct($orderList = [])
    {
        $orderService = new OrderListService();
        $this->orderList = $orderList;
        parent::__construct($orderService, self::TITLE);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->addNoTitle([
            'Item No',
            'Part No.',
            'Color',
            'Part Name',
            'Quantity',
            'Price (JPY)',
            'Total Amount (JPY)',
            'Packing',
            'Box size (S,W,H)',
            'Original (JS,TS,KS)'
        ], $this->type);
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        $this->totalRow = count($this->orderList);
        return collect($this->orderList);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->orderList[0]['contract_code'];
    }

    /**
     * Mapping data
     *
     * @param OrderList $row
     * @return array
     */
    public function map($row): array
    {
        $currentRow = $this->startNum + 9;
        return $this->transform([
            $row['part_code'],
            $row['part_color_code'],
            $row['part']['name'] ?? '',
            $row['actual_quantity'],
            '',
            '=E' . ($currentRow + 1) . '*F' . ($currentRow + 1),
            '',
            '',
            ''
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->buildCellHeaders($event);

                $this->totalCell = count($this->headings()) - 1;
                $totalRow = $this->totalRow + 9;

                $alphabetHeaderItemCell = range('A', 'M');

                foreach ($alphabetHeaderItemCell as $columnName) {
                    $mergedHeaderItem = $columnName . "8:" . $columnName . "9";
                    $event->sheet->getStyle($mergedHeaderItem)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN
                            ],
                        ],
                        'alignment' => ['wrapText' => true],
                        'font' => ['bold' => true]
                    ]);
                }

                foreach ($event->sheet->getRowDimensions() as $rd) {
                    $rd->setRowHeight(-1);
                }
                $this->buildCellTotal($event, $totalRow);

                $cellRange = 'A' .$this->totalRow . ':M' . ($this->totalRow + 9);

                $event->sheet->getStyle($cellRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN
                        ],
                    ],
                    'alignment' => ['wrapText' => true]
                ]);
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
            4 => ['font' => ['bold' => true]],
            5 => ['font' => ['bold' => true]],
            6 => ['font' => ['bold' => true]],
            7 => ['font' => ['bold' => true]],
            8 => ['font' => ['bold' => true]]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 15,
            'C' => 10,
            'D' => 20,
            'E' => 20,
            'F' => 20,
            'H' => 15,
            'I' => 15,
            'J' => 15,
            'K' => 20,
            'L' => 20,
            'M' => 20,
        ];
    }

    /**
     * @param Cell $cell
     * @param $value
     * @return bool
     * @throws Exception
     */
    public function bindValue(Cell $cell, $value): bool
    {
        if (is_numeric($value) && $cell->getColumn() != 'C') {
            if ($value == 0) {
                $value = '';
            } else {
                $cell->setValueExplicit($value, DataType::TYPE_NUMERIC);
                return true;
            }
        }
        // else return default behavior
        return parent::bindValue($cell, $value);
    }

    /**
     * @param $event
     * @return void
     */
    private function buildCellHeaders($event)
    {
        $event->sheet->setCellValue('A1', 'Order No.');
        $event->sheet->getDelegate()->mergeCells('A1:B1');
        $event->sheet->setCellValue('A2', 'ETA');
        $event->sheet->getDelegate()->mergeCells('A2:B2');
        $event->sheet->setCellValue('A3', 'Part group');
        $event->sheet->getDelegate()->mergeCells('A3:B3');
        $event->sheet->setCellValue('A4', 'Supplier');
        $event->sheet->getDelegate()->mergeCells('A4:B4');
        $event->sheet->setCellValue('A5', 'Plant Code');
        $event->sheet->getDelegate()->mergeCells('A5:B5');
        $event->sheet->setCellValue('A6', 'Transportation Mode');
        $event->sheet->getDelegate()->mergeCells('A6:B6');

        $event->sheet->setCellValue('C1', $this->orderList[0]['contract_code'] ?? '');
        $event->sheet->getDelegate()->mergeCells('C1:D1');

        $event->sheet->setCellValue('C2', $this->orderList[0]['eta'] ? Carbon::createFromFormat('Y-m-d', $this->orderList[0]['eta'])->format('d/m/Y') : null);
        $event->sheet->getDelegate()->mergeCells('C2:D2');

        $event->sheet->setCellValue('C3', $this->orderList[0]['part_group'] ?? '');
        $event->sheet->getDelegate()->mergeCells('C3:D3');

        $event->sheet->setCellValue('C4', $this->orderList[0]['supplier_code'] ?? '');
        $event->sheet->getDelegate()->mergeCells('C4:D4');

        $event->sheet->setCellValue('C5', $this->orderList[0]['plant_code'] ?? '');
        $event->sheet->getDelegate()->mergeCells('C5:D5');

        $event->sheet->setCellValue('C6', '');
        $event->sheet->getDelegate()->mergeCells('C6:D6');

//        Set header table
        $event->sheet->getDelegate()->mergeCells('A8:A9');
        $event->sheet->setCellValue('A8', $this->headings()[0]);
        $event->sheet->getDelegate()->mergeCells('B8:B9');
        $event->sheet->setCellValue('B8', $this->headings()[1]);
        $event->sheet->getDelegate()->mergeCells('C8:C9');
        $event->sheet->setCellValue('C8', $this->headings()[2]);
        $event->sheet->getDelegate()->mergeCells('D8:D9');
        $event->sheet->setCellValue('D8', $this->headings()[3]);
        $event->sheet->getDelegate()->mergeCells('E8:E9');
        $event->sheet->setCellValue('E8', $this->headings()[4]);
        $event->sheet->getDelegate()->mergeCells('F8:F9');
        $event->sheet->setCellValue('F8', $this->headings()[5]);
        $event->sheet->getDelegate()->mergeCells('G8:G9');
        $event->sheet->setCellValue('G8', $this->headings()[6]);

        $monthPlusOne = '';
        $monthPlusTwo = '';
        $monthPlusThree = '';
        $monthYearTargetSpanTo = $this->orderList[0]['target_plan_to'] ? DateTimeHelper::getMonthYearFromWeekDefinition($this->orderList[0]['target_plan_to']) : null;
        if ($monthYearTargetSpanTo) {
            $monthYearTargetSpanToObj = Carbon::create($monthYearTargetSpanTo['year'], $monthYearTargetSpanTo['month']);
            $monthPlusOne = $monthYearTargetSpanToObj->addMonth()->format('M.y');
            $monthPlusTwo = $monthYearTargetSpanToObj->addMonth()->format('M.y');
            $monthPlusThree = $monthYearTargetSpanToObj->addMonth()->format('M.y');
        }

        $event->sheet->getDelegate()->mergeCells('H8:J8');
        $event->sheet->setCellValue('H8', 'Forecast');
        $event->sheet->setCellValue('H9', $monthPlusOne);
        $event->sheet->setCellValue('I9', $monthPlusTwo);
        $event->sheet->setCellValue('J9', $monthPlusThree);

        $event->sheet->getDelegate()->mergeCells('K8:K9');
        $event->sheet->setCellValue('K8', $this->headings()[7]);
        $event->sheet->getDelegate()->mergeCells('L8:L9');
        $event->sheet->setCellValue('L8', $this->headings()[8]);
        $event->sheet->getDelegate()->mergeCells('M8:M9');
        $event->sheet->setCellValue('M8', $this->headings()[9]);


        $event->sheet->getStyle('A1:D6')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ],
            ],
            'font' => ['bold' => true]
        ]);
        $event->sheet->getDelegate()->getStyle('H8:J8')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);


        $event->sheet->getDelegate()->freezePane('C10');
    }

    /**
     * @param $event
     * @param $totalRow
     * @return void
     */
    private function buildCellTotal($event, $totalRow)
    {
        $endRow = $totalRow + 1;
        $event->sheet->setCellValue('C' . $endRow, 'Total');
        $event->sheet->getDelegate()->mergeCells('C' . $endRow . ':E' . $endRow);

        $event->sheet->setCellValue('E' . $endRow, '=SUM(E10:E' . $totalRow . ')');
        $event->sheet->setCellValue('F' . $endRow, '=SUM(F10:F' . $totalRow . ')');
        $event->sheet->setCellValue('G' . $endRow, '=SUM(G10:G' . $totalRow . ')');
        $event->sheet->getStyle('A' . $endRow . ':M' . $endRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ],
            ],
        ])->getFont()->setBold(true);
    }
}
