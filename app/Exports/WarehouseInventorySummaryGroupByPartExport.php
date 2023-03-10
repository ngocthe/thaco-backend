<?php

namespace App\Exports;

use App\Models\Warehouse;
use App\Models\WarehouseInventorySummary;
use App\Services\WarehouseInventorySummaryService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class WarehouseInventorySummaryGroupByPartExport extends BaseExport
{
    const TITLE = 'WH Inventory Summary (Part)';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 16;

    /**
     * @var array
     */
    protected array $warehouseCodes;

    /**
     * @var array
     */
    protected array $warehouseCodesAndTypes;

    /**
     * @var array
     */
    protected array $addHeadings;

    /**
     * @var WarehouseInventorySummaryService
     */
    protected $modelService;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new WarehouseInventorySummaryService, self::TITLE, $fileTitle);
        $this->prepareHeadingAndWarehouseCode();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        $headings = [
            'Part No.',
            'Part Color Code',
            'Unit of Measure',
        ];
        array_push($headings, ...$this->addHeadings);
        return $this->addNoTitle($headings, $this->type);
    }

    /**
     * Mapping data
     *
     * @param WarehouseInventorySummary $row
     * @return array
     */
    public function map($row): array
    {
        $data = [
            $row->part_code,
            $row->part_color_code,
            $row->unit,
        ];
        $warehouseCodes = explode(',', $row->warehouse_codes);
        $quantity = explode(',', $row->quantity);
        $sum = 0;
        $quantityInWarehouse = [];
        foreach ($this->warehouseCodes as $warehouseCode => $warehouseType) {
            $quantityInWarehouse[$warehouseCode] = 0;
        }
        foreach ($warehouseCodes as $key => $wC) {
            $sum += $quantity[$key];
            if (isset($quantityInWarehouse[$wC])) {
                $quantityInWarehouse[$wC] += $quantity[$key];
            } else {
                $quantityInWarehouse[Warehouse::PLANT_WAREHOUSE_CODE] += $quantity[$key];
            }
        }
        $quantityInWarehouse['SUM'] = $sum;

        foreach ($quantityInWarehouse as $qty) {
            $data[] = $qty;
        }
        return $this->transform($data, $this->type == 'xls');
    }

    /**
     * @return LengthAwarePaginator|Builder[]|Collection
     */
    public function collection()
    {
        if ($this->type == 'xls') {
            $collection = $this->modelService->filterGroupByPart(false);
            $this->totalRow = $collection->count();
        } else {
            $collection = $this->modelService->filterGroupByPart();
            $this->totalRow = count($collection);
        }

        return $collection;
    }

    /**
     * @return void
     */
    private function prepareHeadingAndWarehouseCode()
    {
        $this->warehouseCodes = $this->modelService->getWarehouseCodes();
        foreach ($this->warehouseCodes as $warehouseCode => $warehouseType) {
            $this->addHeadings[] = $warehouseCode;
        }
        $this->addHeadings[] = 'SUM';
    }
}
