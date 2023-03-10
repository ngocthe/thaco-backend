<?php

namespace App\Services;

use App\Models\BwhInventoryLog;
use App\Models\BwhOrderRequest;
use App\Models\UpkwhInventoryLog;
use App\Models\WarehouseLocation;

class WarehouseLocationService extends BaseService
{
    protected array $fieldRelation = ['warehouse_location_code'];

    protected array $classRelationDelete = [
        BwhInventoryLog::class
    ];
    /**
     * @return string
     */
    public function model(): string
    {
        return WarehouseLocation::class;
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

        if (isset($params['warehouse_code']) && $this->checkParamFilter($params['warehouse_code'])) {
            $this->whereLike('warehouse_code', $params['warehouse_code']);
        }

        $this->addFilterPlantCode($params);

    }
}
