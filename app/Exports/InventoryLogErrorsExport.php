<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;

class InventoryLogErrorsExport extends BaseExport
{
    use Exportable;

    /**
     * @var array|string[]
     */
    public array $exportTemplateTitle = [
        'in_transit_inventory_log' => InTransitInventoryLogExport::TITLE,
        'bwh_inventory_log' => BwhInventoryLogExport::TITLE,
        'order_point_control' => OrderPointControlExport::TITLE,
        'vietnam_source_log' => VietnamSourceRequestExport::TITLE,
        'warehouse_summary_adjustment' => WarehouseSummaryAdjustmentExport::TITLE,
        'warehouse_logical_adjustment_part' => LogicalInventoryPartAdjustmentExport::TITLE,
        'warehouse_logical_adjustment_msc' => LogicalInventoryMscAdjustmentExport::TITLE
    ];

    /**
     * @var array
     */
    protected array $rowsError;

    /**
     * @var array
     */
    protected array $headingsClass;


    public function __construct($rowsError, $headingsClass, $exportFileName)
    {
        $this->rowsError = $rowsError;
        $this->headingsClass = $headingsClass;
        parent::__construct(null, $this->exportTemplateTitle[$exportFileName]);
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        $this->totalRow = count($this->rowsError);
        return new Collection($this->rowsError);
    }

    public function headings(): array
    {
        foreach ($this->headingsClass as $heading) {
            $headingRows[] = $heading;
        }
        $headingRows[] = 'Attribute';
        $headingRows[] = 'Error Data';
        $headingRows[] = 'Error Message';
        return $headingRows;
    }

    /**
     * Mapping data
     *
     * @param $row
     * @return array
     */
    public function map($row): array
    {
        $data = [];
        foreach ($this->headingsClass as $key => $heading) {
            $data[] = $row[$key];
        }
        $data[] = implode(PHP_EOL, $row['attribute']);
        $data[] = implode(PHP_EOL, $row['value']);
        $data[] = implode(PHP_EOL, $row['error_message']);
        return $this->transform($data, true);
    }
}
