<?php

namespace App\Services;

use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Models\WarehouseSummaryAdjustment;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WarehouseService extends BaseService
{
    protected array $fieldRelation = ['warehouse_code'];

    protected array $classRelationDelete = [
        WarehouseLocation::class,
        WarehouseSummaryAdjustment::class
    ];
    /**
     * @return string
     */
    public function model(): string
    {
        return Warehouse::class;
    }

    /**
     * @var array|string[]
     */
    protected array $defaultRelations = [
        'updatedBy', 'remarkable.updatedBy'
    ];

    /**
     * @param $params
     */
    public function addFilter($params = null)
    {
        if (isset($params['warehouse_type']) && $this->checkParamFilter($params['warehouse_type'])) {
            $this->query->where('warehouse_type', $params['warehouse_type']);
        }
        $this->addFilterPlantCode($params);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param array $attributes
     * @param bool $hasRemark
     * @return array
     * @throws Exception
     */
    public function store(array $attributes, bool $hasRemark = true): array
    {
        if ($attributes['warehouse_type'] == Warehouse::TYPE_PLANT_WH) {
            $hasPlantWarehouse = Warehouse::query()
                ->where([
                    'warehouse_type' => Warehouse::TYPE_PLANT_WH,
                    'plant_code' => $attributes['plant_code']
                ])->first();
            if ($hasPlantWarehouse) {
                return [false, 'Cannot create more than one warehouse has type plant for the current plant.'];
            }
        }
        $parent = $this->query->create($attributes);
        if ($hasRemark) {
            $this->createRemark($parent);
        }

        return [$parent, null];
    }
}
