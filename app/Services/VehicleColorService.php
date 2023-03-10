<?php

namespace App\Services;

use App\Models\PartColor;
use App\Models\ProductionPlan;
use App\Models\VehicleColor;

class VehicleColorService extends BaseService
{
    protected array $fieldRelation = ['vehicle_color_code'];

    protected array $classRelationDelete = [
        PartColor::class,
        ProductionPlan::class
    ];
    /**
     * @return string
     */
    public function model(): string
    {
        return VehicleColor::class;
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

        if (isset($params['vehicle_type']) && $this->checkParamFilter($params['vehicle_type'])) {
            $this->query->where('type', $params['vehicle_type']);
        }

        if (isset($params['name']) && $this->checkParamFilter($params['name'])) {
            $this->query->where('name', $params['name']);
        }

        if (isset($params['ecn_in']) && $this->checkParamFilter($params['ecn_in'])) {
            $this->whereLike('ecn_in', $params['ecn_in']);
        }

        if (isset($params['ecn_out']) && $this->checkParamFilter($params['ecn_out'])) {
            $this->whereLike('ecn_out', $params['ecn_out']);
        }

        $this->addFilterPlantCode($params);

    }
}
