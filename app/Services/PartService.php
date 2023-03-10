<?php

namespace App\Services;

use App\Models\Bom;
use App\Models\BoxType;
use App\Models\BwhInventoryLog;
use App\Models\InTransitInventoryLog;
use App\Models\OrderPointControl;
use App\Models\Part;
use App\Models\PartColor;
use App\Models\PlantInventoryLog;
use App\Models\Procurement;
use App\Models\UpkwhInventoryLog;
use App\Models\WarehouseInventorySummary;
use Illuminate\Database\Eloquent\Model;

class PartService extends BaseService
{
    protected array $fieldRelation = ['part_code'];

    protected array $classRelationDelete = [
        PartColor::class,
        BoxType::class,
        UpkwhInventoryLog::class,
        BwhInventoryLog::class,
        PlantInventoryLog::class,
        WarehouseInventorySummary::class,
        InTransitInventoryLog::class,
        OrderPointControl::class,
        Procurement::class
    ];

    /**
     * @return string
     */
    public function model(): string
    {
        return Part::class;
    }

    /**
     * @var array|string[]
     */
    protected array $defaultRelations = [
        'updatedBy', 'ecnInInfo', 'ecnOutInfo', 'remarkable.updatedBy'
    ];

    /**
     * @param $params
     */
    public function addFilter($params = null)
    {
        if (isset($params['is_with_parent_code']) && $params['is_with_parent_code']) {
            if (isset($params['name']) && $this->checkParamFilter($params['name'])) {
                $this->query->where('name', $params['name']);
            }

            if (isset($params['group']) && $this->checkParamFilter($params['group'])) {
                $this->query->where('group', $params['group']);
            }

            if (isset($params['ecn_in']) && $this->checkParamFilter($params['ecn_in'])) {
                $this->query->where('ecn_in', $params['ecn_in']);
            }

            if (isset($params['ecn_out']) && $this->checkParamFilter($params['ecn_out'])) {
                $this->query->where('ecn_out', $params['ecn_out']);
            }

            $this->addFilterPlantCode($params, false);

        } else {
            $this->addCommonFilter($params);
            $this->addFilterPlantCode($params);
        }

    }

    /**
     * @param $params
     */
    public function addFilterCode($params = null)
    {
        $this->addCommonFilter($params);
        $this->addFilterPlantCode($params, false);
    }

    /**
     * @param $params
     * @return void
     */
    private function addCommonFilter($params) {
        if (isset($params['name']) && $this->checkParamFilter($params['name'])) {
            $this->whereLike('name', $params['name']);
        }

        if (isset($params['group']) && $this->checkParamFilter($params['group'])) {
            $this->whereLike('group', $params['group']);
        }

        if (isset($params['ecn_in']) && $this->checkParamFilter($params['ecn_in'])) {
            $this->whereLike('ecn_in', $params['ecn_in']);
        }

        if (isset($params['ecn_out']) && $this->checkParamFilter($params['ecn_out'])) {
            $this->whereLike('ecn_out', $params['ecn_out']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Model|int $item
     * @param bool $force
     * @return bool|array
     *
     */
    public function destroy($item, bool $force = false)
    {
        /**
         * @var Part $item
         */
        $item = $this->query->findOrFail($item);
        $bom = Bom::query()
            ->where('part_code', $item->code)
            ->where('ecn_in', $item->ecn_in)
            ->where('plant_code', $item->plant_code)
            ->first();

        if (isset($bom)) {
            return false;
        }
        $used = false;
        foreach ($this->classRelationDelete as $model) {
            $model = new $model;
            $modelQuery = $model::query()->where('plant_code', $item->plant_code);
            foreach ($this->fieldRelation as $field) {
                if (in_array($field, $model->getFillable()))
                    $modelQuery->where(function ($query) use ($field, $item) {
                        $query->orWhere($field, $item->code);
                    });
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
}
