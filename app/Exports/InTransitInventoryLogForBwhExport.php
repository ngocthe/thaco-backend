<?php

namespace App\Exports;

use App\Models\InTransitInventoryLog;
use App\Services\InTransitInventoryLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class InTransitInventoryLogForBwhExport extends BaseExport
{
    const TITLE = 'Bonded Warehouse Inventory';
    /**
     * @var int
     */
    protected int $firstColumnWidth = 14;

    /**
     * @var InTransitInventoryLogService
     */
    protected $modelService;

    public function __construct($fileTitle = '')
    {
        parent::__construct(new InTransitInventoryLogService, self::TITLE, $fileTitle);
    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return [
            'Contract No.',
            'Invoice No.',
            'B/L No.',
            'Container No.',
            'Case No.',
            'Date Container Received',
            'Date Container Devanned',
            'Date Case Stored',
            'Warehouse Code',
            'Location Code',
            'Date Case shipped to UPKWH',
            'Plant Code',
            'Defect Status'
        ];
    }

    /**
     * Mapping data
     *
     * @param InTransitInventoryLog $row
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
            '',
            '',
            '',
            '',
            '',
            '',
            $row->plant_code,
            ''
        ], true);
    }

    /**
     * @return Builder[]|Collection
     */
    public function collection()
    {
        $columnsGroup = ['contract_code', 'invoice_code', 'bill_of_lading_code', 'container_code', 'case_code', 'plant_code'];
        $this->modelService->query->select($columnsGroup);
        $this->modelService->buildBasicQuery();
        $collection = $this->modelService->query
            ->groupBy($columnsGroup)
            ->get();
        $this->totalRow = $collection->count();

        return $collection;
    }
}
