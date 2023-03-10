<?php

namespace App\Services;

use App\Models\Bom;
use App\Models\InTransitInventoryLog;
use App\Models\LogicalInventoryPartAdjustment;
use App\Models\OrderPointControl;
use App\Models\PartColor;
use App\Models\PlantInventoryLog;
use App\Models\Procurement;
use App\Models\VietnamSourceLog;
use App\Models\WarehouseSummaryAdjustment;

class PartColorService extends BaseService
{
    protected array $fieldRelation = ['part_color_code'];

    protected array $classRelationDelete = [
        Bom::class,
        Procurement::class,
        InTransitInventoryLog::class,
        PlantInventoryLog::class,
        WarehouseSummaryAdjustment::class,
        LogicalInventoryPartAdjustment::class,
        OrderPointControl::class,
        VietnamSourceLog::class
    ];

    /**
     * @return string
     */
    public function model(): string
    {
        return PartColor::class;
    }

    /**
     * @var array|string[]
     */
    protected array $defaultRelations = [
        'updatedBy', 'ecnInInfo', 'ecnOutInfo', 'remarkable.updatedBy'
    ];

    public function destroy($item, bool $force = false)
    {
        if (is_integer($item)) {
            $item = $this->query->findOrFail($item);
        }
        $used = false;
        foreach ($this->classRelationDelete as $model) {
            $model = new $model;
            $modelQuery = $model::query()->where('part_code', $item->part_code)->where('plant_code', $item->plant_code);
            foreach ($this->fieldRelation as $field) {
                if (in_array($field, $model->getFillable()))
                    $modelQuery->where($field, $item->code);
            }
            if ($modelQuery->count() > 0) {
                $used = true;
                break;
            }
        }
        if ($used) {
            return false;
        }

        return $item->{$force ? 'forceDelete' : 'delete'}();
    }


    /**
     * @param $params
     */
    public function addFilter($params = null)
    {

        if (isset($params['part_code']) && $this->checkParamFilter($params['part_code'])) {
            $this->whereLike('part_code', $params['part_code']);
        }
        $this->addCommonFilter($params);
        $this->addFilterPlantCode($params);

    }

    /**
     * @param $params
     */
    public function addFilterCode($params = null)
    {
        $this->addCommonFilter($params);

        $partCode = $params['part_code'] ?? '';
        $code = $params['part_color_code'] ?? '';
        if ($this->checkParamFilter($partCode) && $this->checkParamFilter($code)) {
            $this->query->where('part_code', $partCode);
        } elseif ($this->checkParamFilter($partCode)) {
            $this->whereLike('part_code', $partCode);
        }

        $this->addFilterPlantCode($params, false);
    }

    /**
     * @param $params
     * @return void
     */
    private function addCommonFilter($params) {
        if (isset($params['name']) && $this->checkParamFilter($params['name'])) {
            $this->query->where('name', $params['name']);
        }

        if (isset($params['interior_code']) && $this->checkParamFilter($params['interior_code'])) {
            $this->whereLike('interior_code', $params['interior_code']);
        }

        if (isset($params['vehicle_color_code']) && $this->checkParamFilter($params['vehicle_color_code'])) {
            $this->whereLike('vehicle_color_code', $params['vehicle_color_code']);
        }

        if (isset($params['ecn_in']) && $this->checkParamFilter($params['ecn_in'])) {
            $this->whereLike('ecn_in', $params['ecn_in']);
        }

        if (isset($params['ecn_out']) && $this->checkParamFilter($params['ecn_out'])) {
            $this->whereLike('ecn_out', $params['ecn_out']);
        }
    }
}
